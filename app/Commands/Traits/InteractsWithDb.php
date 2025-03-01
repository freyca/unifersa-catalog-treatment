<?php

namespace App\Commands\Traits;

use App\Models\Family;
use App\Models\Product;

trait InteractsWithDb
{
    private function searchProductInDb(array $record): ?Product
    {
        $query_array = [
            'ean13' => '',
            'codigo_articulo' => '',
            'referencia_proveedor' => '',
            'codigo_proveedor' => '',
            'descripcion' => '',
            'marca_comercial' => '',
            'familia' => '',
            'caracteristicas' => '',
            'imagen' => '',
            'stock' => '',
            'precio_venta' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        return Product::firstOrCreate($query_array);
    }

    private function searchFamilyInDb(array $record): ?Family
    {
        $query_array = [
            'codigo_proveedor' => '',
            'marca_comercial' => '',
            'caracteristicas' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        return Family::firstOrCreate($query_array);
    }

    private function updateDbWithAiTexts(Product $product, array $record): void
    {
        $family = $product->family;

        if ($family === null) {
            $family = $this->searchFamilyInDb($record);
        }

        $record['processed_with_ai'] = true;
        $family->update($record);

        $product->update([
            'family_id' => $family->id,
        ]);
    }
}
