<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Models\Family;
use App\Services\CsvNormalizer\DiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\ProductCsvNormalizer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use PhpZip\ZipFile;

class DownloadUnifersaCsv extends Command
{
    use InteractsWithCsv;
    use InteractsWithDb;

    protected $signature = 'u:download-csv';

    protected $description = 'Downloads CSV Files from Unifersa FTP';

    private array $files_to_download_with_its_final_names;

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->cron('0 2 * * *');
    }

    public function handle()
    {
        // File downloading
        $this->line('Starting download');
        $this->files_to_download_with_its_final_names = config('custom.files_to_download_with_its_final_names');
        $this->downloadFiles();
        $this->info('Files downloades successfully');

        // Seeding database with product csv
        $this->line('Starting field normalization and inserting in database');
        $this->normalizeProductCsvFieldsAndAddToDb('productos.csv');

        // Marking as discontinued products in discontinued csv
        $this->line('Marking products as discontinued');
        $this->normalizeDiscontinuedCsvFieldsAndAddToDb('descatalogados.csv');

        // Generates family name for products
        $this->line('Generating family names');
        $this->generateFamilyName();

        return self::SUCCESS;
    }

    private function downloadFiles()
    {
        foreach ($this->files_to_download_with_its_final_names as $ftp_file_name => $local_file_name) {
            $this->removeOldLocalFile($local_file_name);

            if (! Storage::disk('unifersa')->exists($ftp_file_name)) {
                continue;
            }

            $this->downloadFile($ftp_file_name, $ftp_file_name);

            if (str_ends_with($ftp_file_name, '.zip')) {
                $ftp_file_name = $this->unzipFile($ftp_file_name, $local_file_name);
            }

            Storage::disk('local')->move($ftp_file_name, $local_file_name);
        }
    }

    private function normalizeDiscontinuedCsvFieldsAndAddToDb(string $csv_file_name): void
    {
        $original_csv = $this->openCsvFileAsRead($csv_file_name);
        $csv_normalizer = app(DiscountinuedCsvNormalizer::class);

        $progressbar = $this->output->createProgressBar($original_csv->count());
        $progressbar->start();

        foreach ($original_csv as $record) {
            $record = $csv_normalizer->getNormalizedNames($record);
            $this->markProductAsDiscontinued($record);
            $progressbar->advance();
        }

        $progressbar->finish();
        $this->line('');
    }

    private function normalizeProductCsvFieldsAndAddToDb(string $csv_file_name): void
    {
        $original_csv = $this->openCsvFileAsRead($csv_file_name);
        $csv_normalizer = app(ProductCsvNormalizer::class);

        $progressbar = $this->output->createProgressBar($original_csv->count());
        $progressbar->start();

        foreach ($original_csv as $record) {
            $record = $csv_normalizer->getNormalizedNames($record);

            // This has no other purpouse than create the products in database
            // It allows us to process further data from db and not csv
            // IMPORTANT: Needs to be done after the normalization
            $product = $this->searchProductInDb($record);
            $family = $this->searchFamilyInDb($record);

            if ($product->family_id === null) {
                $product->update(['family_id' => $family->id]);
            }

            $progressbar->advance();
        }

        $progressbar->finish();
        $this->line('');
    }

    private function removeOldLocalFile(string $file_name): void
    {
        if (Storage::disk('local')->exists($file_name)) {
            Storage::disk('local')->delete($file_name);
        }
    }

    private function downloadFile(string $ftp_file_name, string $local_file_name): void
    {
        $contents = Storage::disk('unifersa')->get($ftp_file_name);
        Storage::disk('local')->put($local_file_name, $contents);
    }

    private function unzipFile(string $ftp_file_name): string
    {
        $downloaded_ftp_file_name = storage_path('app/'.$ftp_file_name);

        $zip = new ZipFile;
        $zip->openFile($downloaded_ftp_file_name);

        if (count($zip->getListFiles()) !== 1) {
            throw new \Exception('ZIP archive has unexpected number of files.', 1);
        }

        $zip->extractTo(storage_path('app/'));

        Storage::disk('local')->delete($ftp_file_name);

        return $zip->getListFiles()[0];

        // Ideally we should close the zip
        // $zip->close();
    }

    public function generateFamilyName()
    {
        $progressbar = $this->output->createProgressBar(Family::count());
        $progressbar->start();

        foreach (Family::all() as $family) {
            $family_name = $family->nombre_familia;

            if ($family_name === null) {
                $family_name = $this->getEqualPartInProductName(
                    $family->products()->pluck('descripcion')
                );
            }

            $friendly_name = $family->nombre_manual;

            if ($friendly_name === null) {
                $friendly_name = Str::deduplicate($family_name);
                $friendly_name = Str::chopEnd($friendly_name, '-');
                $friendly_name = Str::replace('.', '. ', $friendly_name);
                $friendly_name = Str::apa($friendly_name);
            }

            $family->update([
                'nombre_familia' => $family_name,
                'nombre_manual' => $friendly_name,
            ]);

            $progressbar->advance();
        }

        $progressbar->finish();
        $this->line('');
    }

    private function getEqualPartInProductName(Collection $names): string
    {
        // If there's no variations, return product name
        if ($names->count() === 1) {
            return $names[0];
        }

        $exploded_name_0 = explode(' ', $names[0]);
        $exploded_name_1 = explode(' ', $names[1]);
        $words = count($exploded_name_0);

        if ($words === 1) {
            return $names[0];
        }

        $counter = 1;
        while ($counter < $words) {
            $substring_comparison_product_0 = implode(' ', array_slice($exploded_name_0, 0, $counter));
            $substring_comparison_product_1 = implode(' ', array_slice($exploded_name_1, 0, $counter));

            if ($substring_comparison_product_0 === $substring_comparison_product_1) {
                $match = $substring_comparison_product_0;
                $counter++;

                continue;
            }

            break;
        }

        if (! isset($match)) {
            $match = $substring_comparison_product_0;
        }

        return $match;
    }
}
