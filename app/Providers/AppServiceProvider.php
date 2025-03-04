<?php

namespace App\Providers;

use App\Services\AI\AIService;
use App\Services\AI\OpenAIService;
use App\Services\CsvNormalizer\DiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\ProductCsvNormalizer;
use App\Services\CsvNormalizer\UnifersaDiscountinuedCsvNormalizer;
use App\Services\CsvNormalizer\UnifersaProductCsvNormalizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(AIService::class, OpenAIService::class);
        $this->app->bind(ProductCsvNormalizer::class, UnifersaProductCsvNormalizer::class);
        $this->app->bind(DiscountinuedCsvNormalizer::class, UnifersaDiscountinuedCsvNormalizer::class);
    }

    public function register(): void {}
}
