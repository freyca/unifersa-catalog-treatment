<?php

namespace App\Commands;

use App\Commands\Traits\InteractsWithDb;
use App\Models\AiTexts;
use App\Models\Product;
use App\Models\Variant;
use App\Services\AI\AIService;
use LaravelZero\Framework\Commands\Command;

class ImproveProductTextsWithAi extends Command
{
    use InteractsWithDb;

    protected $signature = 'u:improve-texts-with-ai';

    protected $description = 'Process products with OpenAI to generate some fancy texts';

    private int $ai_failures = 0;

    private int $ai_failure_threshold = 15;

    public function handle(): int
    {
        $variants = Variant::all();

        foreach ($variants as $variant) {
            if ($variant->ai_texts_id !== null) {
                continue;
            }

            $primer_articulo = $variant->codigos_articulos[0];

            $product = Product::where('codigo_articulo', $primer_articulo)->first();

            try {
                $this->line('Processing product with AI. Database id: '.$product->id);
                $ai_provider = $this->aiProvider($product);

                if (is_null($ai_provider)) {
                    $this->line('Cannot process product. Null parameters: '.$product->id);

                    continue;
                }

                $ai_db_row = AiTexts::create([
                    'descripcion_corta' => $ai_provider->shortDescription(),
                    'descripcion_larga' => $ai_provider->longDescription(),
                    'meta_titulo' => $ai_provider->metaTitle(),
                    'meta_descripcion' => $ai_provider->metaDescription(),
                ]);

                $variant->ai_texts_id = $ai_db_row->id;
                $variant->save();

                $this->info('Product successfully processed with AI. Database id: '.$product->id);
            } catch (\Throwable $th) {
                $this->error('Error procesing product with AI: '.$variant->id.' : '.$th->getMessage());
                $this->ai_failures++;

                if ($this->ai_failures > $this->ai_failure_threshold) {
                    throw $th;
                }

                continue;
            }
        }

        return self::SUCCESS;
    }

    private function aiProvider(Product $product): ?AIService
    {
        if (
            is_null($product->nombre_producto) ||
            is_null($product->modelo_producto) ||
            is_null($product->caracteristicas) ||
            is_null($product->familia)
        ) {
            return null;
        }

        $ai_provider = app(
            AIService::class,
            [
                'description' => $product->nombre_producto.' '.$product->modelo_producto,
                'features' => $product->caracteristicas,
                'family' => $product->familia,
            ]
        );

        return $ai_provider;
    }
}
