<?php

namespace App\Providers;

use App\Services\AI\AIService;
use App\Services\AI\OpenAIService;
use App\Services\CsvNormalizer\Discontinued\DiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\Discontinued\UnifersaDiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\Family\FamilyCsvNormalizer;
use App\Services\CsvNormalizer\Family\UnifersaFamilyCsvNormalizer;
use App\Services\CsvNormalizer\Product\ProductCsvNormalizer;
use App\Services\CsvNormalizer\Product\UnifersaProductCsvNormalizer;
use App\Services\CsvNormalizer\Variant\UnifersaVariantCsvNormalizer;
use App\Services\CsvNormalizer\Variant\VariantCsvNormalizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(AIService::class, OpenAIService::class);
        $this->app->bind(ProductCsvNormalizer::class, UnifersaProductCsvNormalizer::class);
        $this->app->bind(DiscountinuedCsvNormalizer::class, UnifersaDiscountinuedCsvNormalizer::class);
        $this->app->bind(FamilyCsvNormalizer::class, UnifersaFamilyCsvNormalizer::class);
        $this->app->bind(VariantCsvNormalizer::class, UnifersaVariantCsvNormalizer::class);
    }

    public function register(): void {}
}
