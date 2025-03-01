<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Services\AI\AIServiceProvider;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Reader;
use League\Csv\Writer;

class ImproveProductTextsWithAi extends Command
{
    use InteractsWithCsv;
    use InteractsWithDb;

    protected $signature = 'u:improve-texts-with-ai';

    protected $description = 'Process products with OpenAI to generate some fancy texts';

    private Reader $original_csv;

    private Writer $modified_csv;

    private int $ai_failures = 0;

    private int $ai_failure_threshold = 15;

    public function handle(): int
    {
        $this->original_csv = $this->openCsvFileAsRead($this->argument('csv_file'));
        $this->modified_csv = $this->openCsvFileAsWrite('processed_with_ai_' . $this->argument('csv_file'));

        $this->modified_csv->insertOne($this->original_csv->getHeader());

        foreach ($this->original_csv as $record) {
            $product = $this->searchProductInDb($record);
            $family = $this->searchFamilyInDb($record);

            if ($family !== null && $family->processed_with_ai === true) {
                $this->modified_csv->insertOne($record);
                $this->line('Skipping product, it has already been processed. Database id: ' . $product->id);

                continue;
            }

            try {
                $this->line('Processing product with AI. Database id: ' . $product->id);
                $ai_provider = $this->aiProvider($record);

                $record['descripcion_corta'] = $ai_provider->shortDescription();
                $record['descripcion_larga'] = $ai_provider->longDescription();
                $record['meta_titulo'] = $ai_provider->metaTitle();
                $record['meta_descripcion'] = $ai_provider->metaDescription();

                $this->modified_csv->insertOne($record);

                $this->updateDbWithAiTexts($product, $record);

                $this->info('Product successfully processed with AI. Database id: ' . $product->id);
            } catch (\Throwable $th) {
                $this->error('Error procesing product with AI: ' . $product->id . ' : ' . $th->getMessage());
                $this->ai_failures++;

                if ($this->ai_failures > $this->ai_failure_threshold) {
                    throw $th;
                }

                continue;
            }
        }

        return self::SUCCESS;
    }

    private function aiProvider(array $record): AIServiceProvider
    {
        $ai_provider = app(
            AIServiceProvider::class,
            [
                'description' => $record['descripcion'],
                'features' => $record['caracteristicas'],
                'family' => $record['familia'],
            ]
        );

        return $ai_provider;
    }
}
