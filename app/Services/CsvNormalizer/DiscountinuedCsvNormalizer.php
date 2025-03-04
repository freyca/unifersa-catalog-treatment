<?php

namespace App\Services\CsvNormalizer;

interface DiscountinuedCsvNormalizer
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
