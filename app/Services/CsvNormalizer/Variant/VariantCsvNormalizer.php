<?php

namespace App\Services\CsvNormalizer\Variant;

interface VariantCsvNormalizer
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
