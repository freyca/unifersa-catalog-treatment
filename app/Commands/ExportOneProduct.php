<?php

namespace App\Commands;

use App\Commands\Traits\UploadsFileToFtp;
use App\Models\Variant;

class ExportOneProduct extends ExportDbToCsv
{
    use UploadsFileToFtp;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-one-product {variant_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports one product to be imported';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $variant = Variant::find($this->argument('variant_id'));

        if (!$variant) {
            $this->error('Variant could not be found');

            return self::FAILURE;
        }

        $export_file_name = config('custom.export_file_names.productos');

        $this->csv = $this->openCsvFileAsWrite($export_file_name);

        $this->addHeaderToCSV();

        $this->exportVariant($variant);

        $this->csv->toString();

        $this->uploadExportedFile($export_file_name);

        $this->info('File succesfylly exported: ' . storage_path('app/' . $export_file_name));

        $this->line('');

        return self::SUCCESS;
    }
}
