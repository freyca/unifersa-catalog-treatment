<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Models\Family;
use App\Models\Product;
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

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports database to a CSV';

    private array $unset_product_fields = [
        'family_id',
    ];

    private array $unset_family_fields = [
        'codigo_proveedor',
        'marca_comercial',
        'caracteristicas',
        'procesado_con_ia',
    ];

    private string $export_file_name;

    public function handle()
    {
        $this->line('Preparing CSV file...');

        $csvExporter = new \Laracsv\Export($this->openCsvFileAsWrite(config('custom.export_file_name')));

        $product_export_fileds = $this->prepareRelationToExport(Product::class, $this->unset_product_fields);
        $family_export_fields = $this->prepareRelationToExport(Family::class, $this->unset_family_fields, 'family');

        $csvExporter->build(Product::all(), array_merge($product_export_fileds, $family_export_fields));

        $this->info('File succesfylly exported: ' . storage_path('app/' . config('custom.export_file_name')));

        $csvExporter->getWriter()->toString();

        return self::SUCCESS;
    }

    private function prepareRelationToExport(string $classname, array $unset_family_fields, string $prefix = ''): array
    {
        $family_export_fields = $classname::first()->getFillable();

        foreach ($unset_family_fields as $key => $unset_family_field) {
            $unset_key = array_search($unset_family_field, $family_export_fields);
            unset($family_export_fields[$unset_key]);
        }

        foreach ($family_export_fields as $key => $value) {
            $family_export_fields[$key] = $prefix === '' ? $value : $prefix . '.' . $value;
        }

        return $family_export_fields;
    }
}
