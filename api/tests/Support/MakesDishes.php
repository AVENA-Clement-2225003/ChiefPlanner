<?php

namespace Tests\Support;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\User;

/** Direct database builders for tests that are not about dish creation itself. */
trait MakesDishes
{
    /** @param list<string> $slots */
    protected function makeDish(User $owner, string $name = 'Plat', array $slots = ['lunch', 'dinner'], bool $shared = false): Dish
    {
        return Dish::create([
            'owner_id' => $owner->id,
            'name' => $name,
            'slots' => $slots,
            'is_shared' => $shared,
        ]);
    }

    /** Adds one ingredient line to a dish; the quantity is for ONE person. */
    protected function addLine(Dish $dish, string $ingredientName, float $perPerson, string $unitId, ?User $ingredientOwner = null): Ingredient
    {
        $ingredient = Ingredient::firstOrCreate(
            ['owner_id' => ($ingredientOwner ?? $dish->owner)->id, 'name_key' => Ingredient::keyFor($ingredientName)],
            ['name' => $ingredientName, 'is_shared' => true],
        );
        $dish->lines()->create(['ingredient_id' => $ingredient->id, 'quantity_per_person' => $perPerson, 'unit_id' => $unitId]);

        return $ingredient;
    }
}
