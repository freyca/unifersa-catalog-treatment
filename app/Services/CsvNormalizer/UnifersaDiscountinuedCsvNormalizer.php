<?php

namespace App\Services\CsvNormalizer;

class UnifersaDiscountinuedCsvNormalizer extends AbstractCsvNormalizer implements ProductCsvNormalizer
{
    protected array $name_equivalences =
        [
            'FECHA' => 'fecha',
            'COD_ART' => 'codigo_articulo',
            'REF_PROV' => 'referencia_proveedor',
            'COD_EAN13' => 'ean13',
            'OBSERVACIONES' => 'observaciones',
        ];
}
