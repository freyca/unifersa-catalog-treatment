<?php

namespace App\Services\AI;

interface AIService
{
    public function __construct(string $description, string $features, string $family);

    public function shortDescription(): string;

    public function longDescription(): string;

    public function metaTitle(): string;

    public function metaDescription(): string;
}
