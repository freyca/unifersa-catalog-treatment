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

    private function markProductAsDiscontinued(array $record): bool
    {
        $query_array = [
            'codigo_articulo' => '',
            'referencia_proveedor' => '',
            'ean13' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        $p = Product::where('codigo_articulo', $query_array['codigo_articulo'])
            ->where('referencia_proveedor', $query_array['referencia_proveedor'])
            ->where('ean13', $query_array['ean13'])
            ->first();

        if (!is_null($p)) {
            return $p->descatalogado = true;
        } else {
            return false;
        }
    }
}
