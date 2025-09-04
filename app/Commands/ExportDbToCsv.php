<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Models\AiTexts;
use App\Models\Family;
use App\Models\Variant;
use App\Services\PriceCalculator;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class ExportDbToCsv extends Command
{
    use InteractsWithCsv;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'u:export-db-to-csv';

    protected array $csv_headers = [
        'codigo_articulo',
        'codigo_principal',
        'ean13',
        'nombre',
        'nombre comercial',
        'modelo',
        'marca',
        'nombre_variante',
        'variante',
        'precio_venta',
        'stock',
        'imagen',
        'familia_raiz',
        'familia_principal',
        'familia',
        'meta_titulo',
        'meta_descripcion',
        'descripcion_corta',
        'descripcion_larga',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports database to a CSV';

    public function handle()
    {
        $this->line('Preparing CSV file...');

        // load the CSV document from a string
        $csv = $this->openCsvFileAsWrite(config('custom.export_file_names.productos'));

        $csv->insertOne($this->csv_headers);

        $all_variants = Variant::all();

        $progressbar = $this->output->createProgressBar($all_variants->count());
        $progressbar->start();

        foreach ($all_variants as $variant) {
            $progressbar->advance();

            $name = Str::apa(Str::lower($variant->nombre_producto));
            $model = Str::apa(Str::lower($variant->modelo_producto));
            $brand = $variant->marca_comercial;
            $commercial_name = $variant->nombre_personalizado ? $variant->nombre_personalizado : $name . ' ' . $model;
            $product_family_code = min($variant->codigos_articulos);
            $image = null;
            $families = null;
            $products = $variant->products();

            if ($products->count() === 0) {
                continue;
            }

            $family_model = $products->first()->family;

            while ($family_model->nombre_variantes === null) {
                $family_model = Family::where('codigo_familia', $family_model->codigo_padre)->first();
            }

            $variants_name = $family_model->nombre_variantes;

            $texts = $variant->aiTexts;

            if ($texts === null) {
                continue;
            }

            $meta_title = Str::replace(PHP_EOL, ' ', $texts->meta_titulo);
            $meta_description = Str::replace(PHP_EOL, ' ', $texts->meta_descripcion);
            $short_description = Str::replace(PHP_EOL, ' ', $texts->descripcion_corta);
            $long_description = Str::replace(PHP_EOL, ' ', $texts->descripcion_larga);

            $counter = 0;
            foreach ($products as $product) {
                if ($image === null) {
                    $image = $product->imagen;
                }

                if ($families === null) {
                    $families = $product->familia;
                }

                $exploded_families = explode('>', $families);
                $root_family = 'Productos';
                $main_family = end($exploded_families);

                if ($product->descatalogado === true) {
                    continue;
                }

                $stock = $product->stock;

                $product_code = $product->id;
                $ean13 = $product->ean13;
                $variant = Str::trim($product->nombre_variante);
                $price = PriceCalculator::calc($product->precio_venta);

                $product_data = [
                    $product_code,
                    $product_family_code,
                    $ean13,
                    $name,
                    $commercial_name,
                    $model,
                    $brand,
                    $variants_name,
                    $variant,
                    $price,
                    $stock,
                    $image,
                    $root_family,
                    $main_family,
                    $families,
                ];

                // For first product, we push AI texts
                if ($counter === 0) {
                    array_push($product_data, $meta_title, $meta_description, $short_description, $long_description);
                } else {
                    array_push($product_data, '', '', '', '');
                }

                $csv->insertOne($product_data);
                $counter++;
            }
        }

        $csv->toString();

        $progressbar->finish();
        $this->line('');

        $this->info('File succesfylly exported: ' . storage_path('app/' . config('custom.export_file_names.productos')));

        return self::SUCCESS;
    }
}
