<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Models\Product;
use App\Services\CsvNormalizer\Discontinued\DiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\Family\FamilyCsvNormalizer;
use App\Services\CsvNormalizer\Product\ProductCsvNormalizer;
use App\Services\CsvNormalizer\Variant\VariantCsvNormalizer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use PhpZip\ZipFile;

class DownloadUnifersaCsv extends Command
{
    use InteractsWithCsv;
    use InteractsWithDb;

    protected $signature = 'u:download-csv';

    protected $description = 'Downloads CSV Files from Unifersa FTP';

    private array $files_to_download;

    private array $files_to_download_with_its_final_names;

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->cron('0 2 * * *');
    }

    public function handle()
    {
        $this->files_to_download = config('custom.files_to_download');
        $this->files_to_download_with_its_final_names = config('custom.files_to_download_with_its_final_names');

        // File downloading
        $this->line('Starting download');
        $this->downloadFiles();
        $this->info('Files downloades successfully');

        // Seeding database with families csv
        $this->line('Inserting families in database');
        $this->normalizeFamilyCsvFieldsAndAddToDb('familias.csv');

        // Creating variants with variants csv
        $this->line('Inserting variants in database');
        $this->normalizeVariantsCsvFieldsAndAddToDb('variantes.csv');

        // Seeding database with product csv
        $this->line('Inserting products in database');
        $this->normalizeProductCsvFieldsAndAddToDb('productos.csv');

        // Adding families to products
        $this->line('Adding product family');
        $this->addFamilyToProducts();

        // Marking as discontinued products in discontinued csv
        $this->line('Marking products as discontinued');
        $this->normalizeDiscontinuedCsvFieldsAndAddToDb('descatalogados.csv');

        return self::SUCCESS;
    }

    private function downloadFiles(): void
    {
        foreach ($this->files_to_download as $ftp_file_name) {
            $this->removeOldLocalFile($ftp_file_name);

            if (! Storage::disk('unifersa')->exists($ftp_file_name)) {
                continue;
            }

            $this->downloadFile($ftp_file_name, $ftp_file_name);

            if (str_ends_with($ftp_file_name, '.zip')) {
                $this->unzipFile($ftp_file_name);
            }

            $this->renameFiles();
        }
    }

    private function renameFiles(): void
    {
        $local_files = Storage::disk('local')->files();

        foreach ($this->files_to_download_with_its_final_names as $ftp_name => $downloaded_name) {
            $matches = preg_grep('/'.$ftp_name.'/', $local_files);

            if (count($matches) !== 0) {
                Storage::disk('local')->move(array_pop($matches), $downloaded_name);
            }
        }

        File::delete(File::glob(storage_path('app/UNIFERSA_SOC_BASE*.txt')));
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

    private function normalizeFamilyCsvFieldsAndAddToDb(string $csv_file_name): void
    {
        $original_csv = $this->openCsvFileAsRead($csv_file_name);
        $csv_normalizer = app(FamilyCsvNormalizer::class);

        $progressbar = $this->output->createProgressBar($original_csv->count());
        $progressbar->start();

        foreach ($original_csv as $record) {
            $record = $csv_normalizer->getNormalizedNames($record);
            $record['descripcion'] = explode('(', $record['descripcion'])[0];

            // The search is for just creating it if not exists
            $this->searchFamilyInDb($record);

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
            $record['nombre_producto'] = ucfirst(strtolower($record['nombre_producto']));

            // This has no other purpouse than create the products in database
            // It allows us to process further data from db and not csv
            // IMPORTANT: Needs to be done after the normalization
            $this->searchProductInDb($record);
            $progressbar->advance();
        }

        $progressbar->finish();
        $this->line('');
    }

    private function addFamilyToProducts(): void
    {
        $products = Product::all();

        $progressbar = $this->output->createProgressBar($products->count());
        $progressbar->start();

        foreach ($products as $product) {
            $family = $this->searchProductFamily($product);
            $product->family_id = $family->id;
            $product->save();

            $progressbar->advance();
        }

        $progressbar->finish();
        $this->line('');
    }

    private function normalizeVariantsCsvFieldsAndAddToDb(string $csv_file_name): void
    {
        $original_csv = $this->openCsvFileAsRead($csv_file_name);
        $csv_normalizer = app(VariantCsvNormalizer::class);

        $progressbar = $this->output->createProgressBar($original_csv->count());
        $progressbar->start();

        foreach ($original_csv as $record) {
            $record = $csv_normalizer->getNormalizedNames($record);
            $record['codigos_articulos'] = explode(',', $record['codigos_articulos']);
            $record['variantes'] = explode(';', $record['variantes']);

            $this->searchVariantInDb($record);

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

    private function unzipFile(string $ftp_file_name): void
    {
        $downloaded_ftp_file_name = storage_path('app/'.$ftp_file_name);

        $zip = new ZipFile;
        $zip->openFile($downloaded_ftp_file_name);

        $zip->extractTo(storage_path('app/'));

        Storage::disk('local')->delete($ftp_file_name);

        $zip->close();
    }
}
