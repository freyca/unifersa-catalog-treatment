<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\UploadsFileToFtp;
use App\Models\Product;
use LaravelZero\Framework\Commands\Command;

class ExportDiscontinuedProductsToCsv extends Command
{
    use InteractsWithCsv, UploadsFileToFtp;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'u:export-discontinued-products-to-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports discontinued products to a CSV';

    protected array $csv_headers = [
        'codigo_articulo',
        'codigo_principal_familia',
        'ean13',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('Preparing CSV file...');

        // load the CSV document from a string
        $csv = $this->openCsvFileAsWrite(config('custom.export_file_names.descontinuados'));

        $csv->insertOne($this->csv_headers);

        $discontinued_products = Product::where('descatalogado', true)->get();

        $progressbar = $this->output->createProgressBar($discontinued_products->count());
        $progressbar->start();

        foreach ($discontinued_products as $discontinued_product) {
            $progressbar->advance();

            $main_code = $discontinued_product->variant()?->codigos_articulos[0] ? ltrim($discontinued_product->variant()->codigos_articulos[0], 0) : null;

            $product_data = [
                $discontinued_product->id,
                $main_code,
                $discontinued_product->ean13,
            ];

            $csv->insertOne($product_data);
        }

        $csv->toString();

        $progressbar->finish();

        $this->uploadExportedFile(config('custom.export_file_names.descontinuados'));

        $this->line('');

        $this->info('File succesfylly exported: ' . storage_path('app/' . config('custom.export_file_names.descontinuados')));

        return self::SUCCESS;
    }
}
