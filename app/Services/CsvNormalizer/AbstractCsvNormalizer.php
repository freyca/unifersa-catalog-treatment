<?php

namespace App\Services\CsvNormalizer;

abstract class AbstractCsvNormalizer
{
    public function getNormalizedHeader(array $foraneous_csv_header): array
    {
        $normalized_data = [];

        foreach ($foraneous_csv_header as $key => $value) {
            if ($value === '') {
                continue;
            }

            $normalized_name = $this->name_equivalences[$this->trimCommas($value)];

            $normalized_data[$key] = $this->trimCommas($normalized_name);
        }

        return $normalized_data;
    }

    public function getNormalizedNames(array $foraneous_csv_keys_and_values): array
    {
        $normalized_data = [];

        foreach ($foraneous_csv_keys_and_values as $key => $value) {
            if ($key === '') {
                continue;
            }

            $normalized_name = $this->name_equivalences[$this->trimCommas($key)];

            $normalized_data[$normalized_name] = $this->trimCommas($value);
        }

        return $normalized_data;
    }

    private static function trimCommas(string $trimmable): ?string
    {
        $trimmed = trim($trimmable, '\'"');

        return $trimmed !== '' ? $trimmed : null;
    }
}
