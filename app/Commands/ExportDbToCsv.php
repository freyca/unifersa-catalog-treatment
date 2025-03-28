<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Models\AiTexts;
use League\Csv\Writer;
use App\Models\Product;
use App\Models\Variant;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Str;
use SplTempFileObject;

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
        'imagen',
        'familia',
        'meta_titulo',
        'meta_descripcion',
        'descripcion_corta',
        'descripcion_larga',
        'variante',
        'precio_venta',
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

        //load the CSV document from a string
        $csv = $this->openCsvFileAsWrite(config('custom.export_file_names.productos'));

        //insert the header
        $csv->insertOne($this->csv_headers);

        foreach (Variant::all() as $variant) {
            $name =  Str::apa($variant->nombre_producto);
            $model = Str::apa($variant->modelo_producto);
            $image = null;
            $family = null;

            $texts = AiTexts::find($variant->ai_texts_id);

            if ($texts === null) {
                continue;
            }

            $meta_title = $texts->meta_titulo;
            $meta_description = $texts->meta_descripcion;
            $short_description = $texts->descripcion_corta;
            $long_description = $texts->descripcion_larga;

            $products = $variant->codigos_articulos;

            foreach ($products as $product_code) {
                $product = Product::where('codigo_articulo', $product_code)->first();

                if ($image === null) {
                    $image = $product->imagen;
                }

                if ($family === null) {
                    $family = $product->familia;
                }

                $product_code = $product->codigo_articulo;
                $variant = $product->nombre_variante;
                $price = $product->precio_venta;

                $csv->insertOne([
                    $product_code,
                    $name,
                    $model,
                    $image,
                    $family,
                    $meta_title,
                    $meta_description,
                    $short_description,
                    $long_description,
                    $variant,
                    $price,
                ]);
            }
        }

        $csv->toString();

        $this->info('File succesfylly exported: ' . storage_path('app/' . config('custom.export_file_names.productos')));

        return self::SUCCESS;
    }
}
