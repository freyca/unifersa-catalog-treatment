<?php

namespace App\Services\CsvNormalizer;

interface CsvNormalizer
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
