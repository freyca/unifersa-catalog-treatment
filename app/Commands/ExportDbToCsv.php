<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\UploadsFileToFtp;
use App\Models\Family;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Writer;

class ExportDbToCsv extends Command
{
    use InteractsWithCsv, UploadsFileToFtp;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'u:export-db-to-csv
                            {--last : Export only products modified in the last 24 hours}';

    protected Writer $csv;

    protected array $csv_headers = [
        'codigo_articulo',
        'codigo_principal',
        'ean13',
        'nombre',
        'nombre_comercial',
        'modelo',
        'marca',
        'nombre_variante',
        'variante',
        'precio_venta',
        'peso',
        'unidad_minima_venta',
        'descripcion_formato_venta',
        'stock',
        'imagen',
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

        $all_variants = $this->getExportableProducts();

        $progressbar = $this->output->createProgressBar($all_variants->count());
        $progressbar->start();

        $chunk_collections = $all_variants->chunk(1000);
        $counter = 0;

        foreach ($chunk_collections as $chunk) {
            if ($counter === 0) {
                $export_file_name = config('custom.export_file_names.productos');
            } else {
                $export_file_name = config('custom.export_file_names.productos') . '-' . $counter;
            }

            $this->csv = $this->openCsvFileAsWrite($export_file_name);
            $this->addHeaderToCSV();

            foreach ($chunk as $variant) {
                $progressbar->advance();

                $this->exportVariant($variant);
            }

            $this->csv->toString();

            $this->uploadExportedFile($export_file_name);

            $this->info('File succesfylly exported: ' . storage_path('app/' . $export_file_name));

            $counter++;
        }

        $progressbar->finish();

        $this->line('');

        return self::SUCCESS;
    }

    private function getExportableProducts(): Collection
    {
        if ($this->option('last')) {
            $this->line('Exporting only last modified rows...');

            return Variant::where('updated_at', '>', Carbon::now()->subDays(2))->get();
        }

        $this->line('Exporting all database rows...');

        return Variant::all();
    }

    protected function exportVariant(Variant $variant): void
    {
        $products = $variant->products();

        if ($products->count() === 0) {
            return;
        }

        $texts = $variant->aiTexts;

        if ($texts === null) {
            return;
        }

        $name = Str::ucfirst(Str::lower($variant->nombre_producto));
        $model = Str::ucfirst(Str::lower($variant->modelo_producto));
        $brand = Str::ucfirst(Str::lower($variant->marca_comercial));
        $commercial_name = $variant->nombre_personalizado ? $variant->nombre_personalizado : $name . ' ' . $model;
        $product_family_code = min($variant->codigos_articulos);
        $image = null;
        $families = null;
        $family_model = $products->first()->family;

        while ($family_model->nombre_variantes === null) {
            $family_model = Family::where('codigo_familia', $family_model->codigo_padre)->first();
        }

        $variants_name = Str::ucfirst($family_model->nombre_variantes);

        $meta_title = Str::replace(PHP_EOL, ' ', $texts->meta_titulo);
        $meta_description = Str::replace(PHP_EOL, ' ', $texts->meta_descripcion);
        $short_description = Str::replace(PHP_EOL, ' ', $texts->descripcion_corta);
        $long_description = Str::replace(PHP_EOL, ' ', $texts->descripcion_larga);

        $counter = 0;
        foreach ($products as $product) {
            if ($product->descatalogado === true) {
                continue;
            }

            if ($image === null) {
                $image = $product->imagen;
            }

            if ($families === null) {
                $families = $product->familia;

                $exploded_families = explode('>', $families);
                $root_family = 'Productos';
                $main_family = end($exploded_families);

                $families = $root_family . '>' . $families;
            }

            $stock = $product->stock;

            $product_code = $product->id;
            $ean13 = $product->ean13;
            $variant = Str::ucfirst(Str::lower(Str::trim($product->nombre_variante)));
            $price = $product->precio_venta;
            $weight = $product->peso_unidad_minima_venta;
            $min_sell_qty = $product->unidad_minima_venta;
            $desc_sell_format = $product->descripcion_formato_venta;

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
                $weight,
                $min_sell_qty,
                $desc_sell_format,
                $stock,
                $image,
                $main_family,
                $families,
            ];

            // For first product, we push AI texts
            if ($counter === 0) {
                array_push($product_data, $meta_title, $meta_description, $short_description, $long_description);
            } else {
                array_push($product_data, '', '', '', '');
            }

            $this->csv->insertOne($product_data);
            $counter++;
        }
    }

    protected function addHeaderToCSV(): void
    {
        $this->csv->insertOne($this->csv_headers);
    }
}
