<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Models\Family;
use App\Services\CsvNormalizer\CsvNormalizerServiceProvider;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Reader;
use League\Csv\Writer;

class AddProductFamilyField extends Command
{
    use InteractsWithCsv;
    use InteractsWithDb;

    protected $signature = 'u:add-product-family';

    protected $description = 'Tries to guess family name from variants';

    private Reader $original_csv;

    private Writer $modified_csv;

    private CsvNormalizerServiceProvider $csv_normalizer;

    public function handle()
    {
        $counter = 0;

        foreach (Family::all() as $family) {
            $this->line('Processing family '.$counter);
            if ($family->nombre_familia !== null) {
                continue;
            }

            $family_name = $this->getEqualPartInProductName(
                $family->products()->pluck('descripcion')
            );

            $family->update([
                'nombre_familia' => $family_name,
            ]);

            $counter++;
        }

        $this->info('Job done');
    }

    private function getEqualPartInProductName(Collection $names): string
    {
        // If there's no variations, return product name
        if ($names->count() === 1) {
            return $names[0];
        }

        $exploded_name_0 = explode(' ', $names[0]);
        $exploded_name_1 = explode(' ', $names[1]);
        $words = count($exploded_name_0);

        if ($words === 1) {
            return $names[0];
        }

        $counter = 1;
        while ($counter < $words) {
            $substring_comparison_product_0 = implode(' ', array_slice($exploded_name_0, 0, $counter));
            $substring_comparison_product_1 = implode(' ', array_slice($exploded_name_1, 0, $counter));

            if ($substring_comparison_product_0 === $substring_comparison_product_1) {
                $match = $substring_comparison_product_0;
                $counter++;

                continue;
            }

            break;
        }

        if (! isset($match)) {
            $match = $substring_comparison_product_0;
        }

        return $match;
    }
}
