<?php

namespace App\Policies;

use App\Models\Ingredient;
use App\Models\User;

class IngredientPolicy
{
    public function manage(User $user, Ingredient $ingredient): bool
    {
        return $ingredient->owner_id === $user->id;
    }
}
