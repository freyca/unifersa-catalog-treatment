<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Models\AiTexts;
use App\Models\Family;
use App\Models\Product;
use App\Models\Variant;
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
        'nombre',
        'modelo',
        'variantes',
        'precio_venta',
        'imagen',
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

        // insert the header
        // Ideally we iterate over family variant types, but we need to control order, so we will
        // insert it manuyally
        // $family_variant_names = Family::groupBy('nombre_variantes')->pluck('nombre_variantes');
        // foreach ($family_variant_names as $family_variant_name) {
        //    if ($family_variant_name === null) {
        //        continue;
        //    }
        //
        //    array_push($this->csv_headers, $family_variant_name);
        // }

        array_push($this->csv_headers, 'formato');
        array_push($this->csv_headers, 'medida');
        array_push($this->csv_headers, 'variante');

        $csv->insertOne($this->csv_headers);

        $all_variants = Variant::all();

        $progressbar = $this->output->createProgressBar($all_variants->count());
        $progressbar->start();

        foreach ($all_variants as $variant) {
            $progressbar->advance();

            $name = Str::apa(Str::lower($variant->nombre_producto));
            $model = Str::apa(Str::lower($variant->modelo_producto));
            $image = null;
            $family = null;
            $products = $variant->products();

            if ($products->count() === 0) {
                continue;
            }

            $family_model = $products->first()->family;

            while ($family_model->nombre_variantes === null) {
                $family_model = Family::where('codigo_familia', $family_model->codigo_padre)->first();
            }

            $nombre_variantes = $family_model->nombre_variantes;

            $texts = AiTexts::find($variant->ai_texts_id);

            if ($texts === null) {
                continue;
            }

            $meta_title = Str::replace(PHP_EOL, ' ', $texts->meta_titulo);
            $meta_description = Str::replace(PHP_EOL, ' ', $texts->meta_descripcion);
            $short_description = Str::replace(PHP_EOL, ' ', $texts->descripcion_corta);
            $long_description =Str::replace(PHP_EOL, ' ',  $texts->descripcion_larga);

            $counter = 0;
            foreach ($products as $product) {
                if ($image === null) {
                    $image = $product->imagen;
                }

                if ($family === null) {
                    $family = $product->familia;
                }

                if ($product->descatalogado === true) {
                    continue;
                }

                $product_code = $product->id;
                $variant = Str::trim($product->nombre_variante);
                $price = $product->precio_venta;

                $product_data = [
                    $product_code,
                    $name,
                    $model,
                    $variant,
                    $price,
                    $image,
                    $family,
                ];

                // For first product, we push AI texts
                if ($counter === 0) {
                    array_push($product_data, $meta_title, $meta_description, $short_description, $long_description);
                } else {
                    array_push($product_data, '', '', '', '');
                }

                match ($nombre_variantes) {
                    'formato' => array_push($product_data, $product->nombre_variante, '', ''),
                    'medida' => array_push($product_data, '', $product->nombre_variante, ''),
                    'variante' => array_push($product_data, '', '', $product->nombre_variante),
                };

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
