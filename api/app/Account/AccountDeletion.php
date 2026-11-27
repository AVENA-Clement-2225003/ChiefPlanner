<?php

namespace App\Account;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Erases everything a user owns (RGPD). Ingredients stay: they are shared names, not personal data. Their identity
 * in Authentik is not touched.
 */
class AccountDeletion
{
    public function erase(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Dishes first: their ingredient lines would otherwise keep the user's ingredients in use.
            $user->dishes()->each(fn ($dish) => $dish->delete());

            // Owned data (meals, preferences, MealFlows, packs, extras, spendings...) goes with the foreign keys of this delete.
            // Ingredients are not personal data: they are shared names, and stay.
            $user->delete();
        });
    }
}
