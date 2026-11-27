<?php

namespace App\Policies;

use App\Models\Dish;
use App\Models\User;

class DishPolicy
{
    public function view(User $user, Dish $dish): bool
    {
        return $dish->isOwnedBy($user) || $dish->is_shared || $dish->subscribers->contains($user);
    }

    public function manage(User $user, Dish $dish): bool
    {
        return $dish->isOwnedBy($user);
    }

    /** Adopting means taking a dish somebody else shares with the Community. */
    public function subscribe(User $user, Dish $dish): bool
    {
        return ! $dish->isOwnedBy($user) && $dish->is_shared;
    }
}
