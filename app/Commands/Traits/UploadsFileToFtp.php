<?php

namespace App\Commands\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadsFileToFtp
{
    private function uploadExportedFile(string $file_name): void
    {
        if (Storage::disk('industrialferretera')->exists($file_name)) {
            Storage::disk('industrialferretera')->delete($file_name);
        }

        $contents = Storage::disk('local')->get($file_name);
        Storage::disk('industrialferretera')->put($file_name, $contents);
    }
}
