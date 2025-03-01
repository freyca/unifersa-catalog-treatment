<?php

namespace App\Providers;

use App\Services\AI\AIServiceProvider;
use App\Services\AI\OpenAIServiceProvider;
use App\Services\CsvNormalizer\CsvNormalizerServiceProvider;
use App\Services\CsvNormalizer\UnifersaCsvNormalizer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(AIServiceProvider::class, OpenAIServiceProvider::class);
        $this->app->bind(CsvNormalizerServiceProvider::class, UnifersaCsvNormalizer::class);
    }

    public function register(): void {}
}
