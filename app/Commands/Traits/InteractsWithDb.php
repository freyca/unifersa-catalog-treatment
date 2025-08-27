<?php

namespace App\Commands\Traits;

use App\Models\Family;
use App\Models\Product;
use App\Models\Variant;

trait InteractsWithDb
{
    private function searchProductInDb(array $record): ?Product
    {
        $query_array = [
            'id' => '',
            'ean13' => '',
            'referencia_proveedor' => '',
            'codigo_proveedor' => '',
            'descripcion' => '',
            'marca_comercial' => '',
            'familia' => '',
            'caracteristicas' => '',
            'imagen' => '',
            'stock' => '',
            'precio_venta' => '',
            'nombre_producto' => '',
            'modelo_producto' => '',
            'nombre_variante' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        return Product::firstOrCreate(
            ['id' => $query_array['id']],
            $query_array
        );
    }

    private function searchProductFamily(Product $product): Family
    {
        $all_families = explode('>', $product->familia);

        return Family::where('descripcion', end($all_families))->first();
    }

    private function searchVariantInDb(array $record): Variant
    {
        $query_array = [
            'nombre_producto' => '',
            'modelo_producto' => '',
            'codigo_proveedor' => '',
            'marca_comercial' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        $variant = Variant::firstOrCreate($query_array);
        $variant->update($record);

        return $variant;
    }

    private function searchFamilyInDb(array $record): ?Family
    {
        $query_array = [
            'codigo_familia' => '',
            'descripcion' => '',
            'codigo_padre' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        return Family::firstOrCreate($query_array);
    }

    private function markProductAsDiscontinued(array $record): void
    {
        $query_array = [
            'id' => '',
            'referencia_proveedor' => '',
            'ean13' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        $p = Product::where('id', $query_array['id'])
            ->where('referencia_proveedor', $query_array['referencia_proveedor'])
            ->where('ean13', $query_array['ean13'])
            ->first();

        if (! is_null($p)) {
            $p->descatalogado = true;
            $p->save();
        }
    }
}
