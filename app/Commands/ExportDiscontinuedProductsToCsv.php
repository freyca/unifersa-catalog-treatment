<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Models\Product;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ExportDiscontinuedProductsToCsv extends Command
{
    use InteractsWithCsv;

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

            $product_data = [
                $discontinued_product->id,
                $discontinued_product->family()->codigos_articulos[0] ?? null,
                $discontinued_product->ean13,
            ];

            $csv->insertOne($product_data);
        }

        $csv->toString();

        $progressbar->finish();
        $this->line('');

        $this->info('File succesfylly exported: ' . storage_path('app/' . config('custom.export_file_names.descontinuados')));

        return self::SUCCESS;
    }
}
