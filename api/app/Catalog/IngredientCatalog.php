<?php

namespace App\Catalog;

use App\Models\Ingredient;
use App\Models\User;

class IngredientCatalog
{
    /**
     * Reuses the ingredient with the same name if the user can see one (theirs first, then the Community's),
     * otherwise creates it for them. Returns the ingredient and whether it was just created.
     *
     * @return array{0: Ingredient, 1: bool}
     */
    public function findOrCreate(User $user, string $name, bool $shared = true): array
    {
        $key = Ingredient::keyFor($name);

        $existing = Ingredient::query()
            ->visibleTo($user)
            ->where('name_key', $key)
            ->orderByRaw('owner_id = ? desc', [$user->id])
            ->orderBy('id')
            ->first();
        if ($existing) {
            return [$existing, false];
        }

        $created = Ingredient::create([
            'owner_id' => $user->id,
            'name' => trim($name),
            'name_key' => $key,
            'is_shared' => $shared,
        ]);

        return [$created, true];
    }
}
