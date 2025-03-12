<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithCsv;
use App\Commands\Traits\InteractsWithDb;
use App\Models\AiTexts;
use App\Models\Product;
use App\Services\AI\AIService;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Reader;
use League\Csv\Writer;

class ImproveProductTextsWithAi extends Command
{
    use InteractsWithDb;

    protected $signature = 'u:improve-texts-with-ai';

    protected $description = 'Process products with OpenAI to generate some fancy texts';

    private int $ai_failures = 0;

    private int $ai_failure_threshold = 15;

    public function handle(): int
    {
        $products = Product::all();

        foreach ($products as $product) {
            if ($product->family->necesita_revision_manual !== true) {
                continue;
            }

            // This is for products with a family
            if ($product->family->procesado_con_ia === true) {
                $product->ai_texts_id = $product->family->products()->first()->ai_texts_id;
                $product->save();

                $this->line('Skipping product, it has already been processed. Database id: ' . $product->id);

                continue;
            }

            try {
                $this->line('Processing product with AI. Database id: ' . $product->id);
                $ai_provider = $this->aiProvider($product);

                $ai_db_row = AiTexts::create([
                    'descripcion_corta' => $ai_provider->shortDescription(),
                    'descripcion_larga' => $ai_provider->longDescription(),
                    'meta_titulo' => $ai_provider->metaTitle(),
                    'meta_descripcion' => $ai_provider->metaDescription(),
                ]);

                $product->ai_texts_id = $ai_db_row->id;
                $product->save();

                $product->family->procesado_con_ia = true;
                $product->family->save();

                foreach ($product->family->products as $related_product) {
                    $related_product->ai_texts_id = $product->ai_texts_id;
                    $related_product->save();
                }

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

    private function aiProvider(Product $product): AIService
    {
        $ai_provider = app(
            AIService::class,
            [
                'description' => $product->descripcion,
                'features' => $product->caracteristicas,
                'family' => $product->familia,
            ]
        );

        return $ai_provider;
    }
}
