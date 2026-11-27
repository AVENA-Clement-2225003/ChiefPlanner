<?php

namespace App\Policies;

use App\Models\MealFlow;
use App\Models\User;

class MealFlowPolicy
{
    public function manage(User $user, MealFlow $mealFlow): bool
    {
        return $mealFlow->owner_id === $user->id;
    }
}
