<?php

namespace App\Commands;

use App\Models\Family;
use LaravelZero\Framework\Commands\Command;

class ManualReviewProductFamilies extends Command
{
    protected $signature = 'u:manual-review-product-families';

    protected $description = 'Prints products on the screen with its asociated families so a user can check if everything is allright';

    private bool $analise_families_with_one_product = false;

    public function handle()
    {
        if ($this->confirm('Want to analyse products with one family?')) {
            $this->analise_families_with_one_product = true;
        }

        $first_family_id = 0;
        if ($this->confirm('Want to skip to family with ID X?')) {
            $first_family_id = $this->ask('Enter the family id');
        }

        foreach (Family::all() as $family) {
            // Family id 1 is the box for products without family
            if ($family->id === 1) {
                continue;
            }

            if ($family->id < $first_family_id) {
                continue;
            }

            $family->fresh();

            if (! $family->necesita_revision_manual) {
                continue;
            }

            system('clear');
            $this->askUserForValidation($family);
        }
    }

    private function askUserForValidation(Family $family): void
    {
        $products = $family->products;

        // Families could have no products
        if ($products->count() === 0) {
            return;
        }

        if ($products->count() === 1 && $this->analise_families_with_one_product === false) {
            return;
        }

        $this->info('Familia ' . $family->id . ' - ' . $family->nombre_manual);
        $counter = 0;
        foreach ($products as $product) {
            $this->line($counter . ' - ' . $product->descripcion);
            $counter++;
        }

        if ($this->confirm('Is everything ok?')) {
            $family->necesita_revision_manual = false;
            $family->save();

            return;
        }

        $option = $this->menu('Options', [
            'Split products in separate families',
            'Assign family name manually',
            'Search a better family for product(s)',
            'Assign to family with id 0',
        ])->setTitle('UNIFERSA catalog treatment options')
            ->addLineBreak('-')
            ->setForegroundColour('green')
            ->setBackgroundColour('black')
            ->open();

        match ($option) {
            0 => $this->splitProductsInSeparateFamilies($products),
            1 => $this->assignFamilyNameManually($family),
            2 => $this->searchABetterFamily($products),
            3 => $this->assignToFamilyWithId0($products),
            default => true,
        };
    }

    private function splitProductsInSeparateFamilies($products)
    {
        $this->error('This function does not works by now');
    }

    private function assignFamilyNameManually(Family $family): void
    {
        foreach ($family->products as $product) {
            $this->line($product->descripcion);
        }

        $this->line('Previous name: ' . $family->nombre_manual);
        $name = $this->ask('Give the name');

        $family->nombre_manual = $name;
        $family->necesita_revision_manual = false;
        $family->save();
    }

    private function assignToFamilyWithId0($products)
    {
        foreach ($products as $product) {
            $product->family_id = 1;
            $product->save();
        }
    }

    private function searchABetterFamily($products): void
    {
        $counter = 0;
        foreach ($products as $product) {
            if ($counter === 0) {
                $this->line($product->family->nombre_familia);
                $counter++;
            }

            $this->line($product->descripcion);
        }
        $this->line('----------------');

        $search_term = $this->ask('Give search query');

        $found_families = Family::where('nombre_familia', 'like', "%{$search_term}%")->get();

        foreach ($found_families as $family) {
            $this->line($family->id . ' - ' . $family->nombre_familia);
            foreach ($family->products as $product) {
                $this->line($product->descripcion);
            }

            $this->line('---------------');
        }

        if ($this->confirm('Is any of this families suitable for the product(s)?')) {
            $family_id = $this->ask('Give the family id -number before the name-');

            foreach ($products as $product) {
                $product->family_id = intval($family_id);
                $product->save();
            }

            return;
        }

        $this->searchABetterFamily($family);
    }
}
