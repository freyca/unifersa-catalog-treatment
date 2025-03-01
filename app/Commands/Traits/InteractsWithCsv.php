<?php

namespace App\Commands\Traits;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Csv\Bom;
use League\Csv\Reader;
use League\Csv\Writer;

trait InteractsWithCsv
{
    protected function openCsvFileAsRead(string $csv_file_name, string $delimiter = '|', $header_offset = 0): Reader
    {
        if (! $this->validatesCsvFileExists($csv_file_name)) {
            throw new Exception('Provided csv file does not exists');
        }

        $reader = Reader::createFromPath(storage_path('app/'.$csv_file_name), 'r');
        $reader->setHeaderOffset($header_offset);
        $reader->setDelimiter($delimiter);

        if (Bom::tryFromSequence($reader)?->isUtf16() ?? false) {
            $reader->appendStreamFilterOnRead('convert.iconv.UTF-16/UTF-8');
        }

        return $reader;
    }

    protected function openCsvFileAsWrite(string $csv_file_name, string $delimiter = '|'): Writer
    {
        if ($this->validatesCsvFileExists($csv_file_name)) {
            Storage::disk('local')->move($csv_file_name, $csv_file_name.Str::random());
        }

        $writer = Writer::createFromPath(storage_path('app/'.$csv_file_name), 'w+');
        $writer->setDelimiter($delimiter);

        return $writer;
    }

    protected function addHeadersToCsv(array $headers, array $headers_to_add): array
    {
        foreach ($headers_to_add as $new_header) {
            array_push($headers, $new_header);
        }

        return $headers;
    }

    protected function addHeaderValuesToCsvRow(array $record, array $headers_to_add): array
    {
        foreach ($headers_to_add as $new_header) {
            $record["$new_header"] = '';
        }

        return $record;
    }

    protected function validatesCsvFileExists(string $csv_file_name): bool
    {
        return Storage::disk('local')->exists($csv_file_name);
    }
}
