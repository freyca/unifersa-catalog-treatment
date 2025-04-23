<?php

namespace App\Services\CsvNormalizer\Discontinued;

use App\Services\CsvNormalizer\AbstractCsvNormalizer;

class UnifersaDiscountinuedCsvNormalizer extends AbstractCsvNormalizer implements DiscountinuedCsvNormalizer
{
    protected array $name_equivalences =
        [
            'FECHA' => 'fecha',
            'COD_ART' => 'id',
            'REF_PROV' => 'referencia_proveedor',
            'COD_EAN13' => 'ean13',
            'OBSERVACIONES' => 'observaciones',
        ];
}
