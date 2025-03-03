<?php

namespace App\Providers;

use App\Services\AI\AIService;
use App\Services\AI\OpenAIService;
use App\Services\CsvNormalizer\CsvNormalizer;
use App\Services\CsvNormalizer\UnifersaCsvNormalizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(AIService::class, OpenAIService::class);
        $this->app->bind(CsvNormalizer::class, UnifersaCsvNormalizer::class);
    }

    public function register(): void {}
}
