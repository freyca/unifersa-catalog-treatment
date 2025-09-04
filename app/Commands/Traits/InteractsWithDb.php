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
            'unidad_facturacion' => '',
            'descripcion_formato_venta' => '',
            'unidades_formato_venta' => '',
            'unidad_minima_venta' => '',
            'ancho_especial' => '',
            'agreement_dangerous_road_especial' => '',
            'logistica_especial' => '',
            'peso_unidad_minima_venta' => '',
            'tipo_iva' => '',
        ];

        foreach ($record as $key => $value) {
            if (isset($query_array[$key])) {
                $query_array[$key] = $value;
            }
        }

        return Product::updateOrCreate(
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

        $variant = Variant::updateOrCreate($query_array, $record);

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

        return Family::updateOrCreate($query_array);
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
