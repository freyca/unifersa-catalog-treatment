<?php

namespace App\Services\CsvNormalizer\Family;

use App\Services\CsvNormalizer\AbstractCsvNormalizer;

class UnifersaFamilyCsvNormalizer extends AbstractCsvNormalizer implements FamilyCsvNormalizer
{
    protected array $name_equivalences =
        [
            'CODIGO_FAMILIA' => 'codigo_familia',
            'DESCRIPCION' => 'descripcion',
            'CODIGO_PADRE' => 'codigo_padre',
        ];
}
