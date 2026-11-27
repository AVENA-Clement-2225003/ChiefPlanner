<?php

namespace App\Catalog;

use App\Models\Ingredient;

class IngredientCatalog
{
    /**
     * Returns the ingredient with this name (compared without case, accents or extra spaces), creating it when
     * nobody has used the name yet. The second value tells whether it was just created.
     *
     * @return array{0: Ingredient, 1: bool}
     */
    public function findOrCreate(string $name): array
    {
        $ingredient = Ingredient::firstOrCreate(
            ['name_key' => Ingredient::keyFor($name)],
            ['name' => trim($name)],
        );

        return [$ingredient, $ingredient->wasRecentlyCreated];
    }
}
