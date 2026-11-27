<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MealFlow extends Model
{
    protected $fillable = ['owner_id', 'name'];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'meal_flow_dish');
    }
}
