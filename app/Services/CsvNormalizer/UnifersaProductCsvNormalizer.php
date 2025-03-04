<?php

namespace App\Services\CsvNormalizer;

class UnifersaProductCsvNormalizer extends AbstractCsvNormalizer implements ProductCsvNormalizer
{
    protected array $name_equivalences =
    [
        'FECHA_TARIFA' => 'fecha_tarifa',
        'COD_ART' => 'codigo_articulo',
        'DESCRIPCION' => 'descripcion',
        'COD_PROV' => 'codigo_proveedor',
        'MARCA_COMERCIAL' => 'marca_comercial',
        'REF_PROV' => 'referencia_proveedor',
        'COD_EAN13' => 'ean13',
        'FAMILIA' => 'familia',
        'UND_FRA' => 'unidad_facturacion',
        'DESC_UND_FRA' => 'descripcion_formato_venta',
        'UNDS_FRA' => 'unidades_formato_venta',
        'UND_MIN_VTA' => 'unidad_minima_venta',
        'PESO_UND_MIN_VTA' => 'peso_unidad_minima_venta',
        'PVP' => 'precio_venta',
        'STOCK_DISPO' => 'stock',
        'IMAGEN' => 'imagen',
        'CARACTERISTICAS' => 'caracteristicas',
        'LARGO_ESPECIAL' => 'largo_especial',
        'ANCHO_ESPECIAL' => 'ancho_especial',
        'PESO_ESPECIAL' => 'peso_especial',
        'ADR_ESPECIAL' => 'agreement_dangerous_road_especial',
        'LOGISTICA_ESPECIAL' => 'logistica_especial',
    ];
}
