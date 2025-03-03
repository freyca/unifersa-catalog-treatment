<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Services\CsvNormalizer\CsvNormalizer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use PhpZip\ZipFile;

class DownloadUnifersaCsv extends Command
{
    use InteractsWithCsv;
    use InteractsWithDb;

    protected $signature = 'u:download-csv';

    protected $description = 'Downloads CSV Files from Unifersa FTP';

    private array $files_to_download_with_its_final_names;

    private array $new_headers_to_add_in_csv = [
        'id_familia',
        'nombre_familia',
        'descripcion_corta',
        'descripcion_larga',
        'meta_titulo',
        'meta_descripcion',
    ];

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->cron('0 2 * * *');
    }

    public function handle()
    {
        $this->line('Starting download');

        $this->files_to_download_with_its_final_names = config('custom.files_to_download_with_its_final_names');

        $this->downloadFiles();

        $this->info('Files downloades successfully');

        $this->line('Starting field normalization and inserting in database');

        $this->normalizeCsvFields('productos.csv');

        $this->info('Job done');

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

    private function normalizeCsvFields(string $csv_file_name): void
    {
        $original_csv = $this->openCsvFileAsRead($csv_file_name);
        $csv_normalizer = app(CsvNormalizer::class);

        $counter = 0;
        foreach ($original_csv as $record) {
            $record = $csv_normalizer->getNormalizedNames($record);
            $record = $this->addHeaderValuesToCsvRow($record, $this->new_headers_to_add_in_csv);

            $this->line('Processing line ' . $counter);

            // This has no other purpouse than create the products in database
            // It allows us to process further data from db and not csv
            // IMPORTANT: Needs to be done after the normalization
            $product = $this->searchProductInDb($record);
            $family = $this->searchFamilyInDb($record);

            if ($product->family_id === null) {
                $product->update(['family_id' => $family->id]);
            }

            $counter++;
        }
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
        $downloaded_ftp_file_name = storage_path('app/' . $ftp_file_name);

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
}
