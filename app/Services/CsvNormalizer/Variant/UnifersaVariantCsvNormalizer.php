<?php

namespace App\Services\CsvNormalizer\Variant;

use App\Services\CsvNormalizer\AbstractCsvNormalizer;

class UnifersaVariantCsvNormalizer extends AbstractCsvNormalizer implements VariantCsvNormalizer
{
    protected array $name_equivalences =
        [
            'FECHA_TARIFA' => 'fecha_tarifa',
            'AGRUPA_DESCRIPCION' => 'nombre_producto',
            'AGRUPA_MODELO' => 'modelo_producto',
            'COD_PROV' => 'codigo_proveedor',
            'MARCA_COMERCIAL' => 'marca_comercial',
            'CODIGO_INICIAL' => 'codigo_inicial',
            'AGRUPA_CODIGOS' => 'codigos_articulos',
            'AGRUPA_MEDIDAS' => 'variantes',
        ];
}
