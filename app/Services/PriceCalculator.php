<?php

namespace App\Services;

class PriceCalculator
{
    public static function calc(string $price): float
    {
        $price = str_replace(',', '.', $price);

        $price = floatval($price);

        return round($price * 0.51 * 1.22 * 1.21, 2);
    }
}
