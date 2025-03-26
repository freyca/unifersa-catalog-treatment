<?php

namespace App\Services\CsvNormalizer\Discontinued;

interface DiscountinuedCsvNormalizer
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
