<?php

namespace App\Account;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Erases everything a user owns (RGPD). The only thing that stays is a shared ingredient other people's dishes
 * still use: it becomes the Community's, with no link left to the person. Their identity in Authentik is not touched.
 */
class AccountDeletion
{
    public function erase(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Dishes first: their ingredient lines would otherwise keep the user's ingredients in use.
            $user->dishes()->each(fn ($dish) => $dish->delete());

            Ingredient::query()->where('owner_id', $user->id)->each(function (Ingredient $ingredient) {
                $ingredient->dishLines()->exists()
                    ? $ingredient->update(['is_shared' => true])
                    : $ingredient->delete();
            });

            // Owned data (meals, preferences, MealFlows, packs, extras, spendings...) and the ownerless
            // ingredients above are handled by the foreign keys of this delete.
            $user->delete();
        });
    }
}
