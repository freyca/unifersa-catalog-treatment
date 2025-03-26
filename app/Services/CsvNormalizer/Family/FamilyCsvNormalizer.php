<?php

namespace App\Services\CsvNormalizer\Family;

interface FamilyCsvNormalizer
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
