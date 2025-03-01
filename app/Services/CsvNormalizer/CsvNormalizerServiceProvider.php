<?php

namespace App\Services\CsvNormalizer;

interface CsvNormalizerServiceProvider
{
    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array;
}
