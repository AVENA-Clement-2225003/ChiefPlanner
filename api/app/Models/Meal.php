<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A Meal of the current Week: a Slot of a day with a dish and a number of servings. */
class Meal extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = ['user_id', 'day', 'slot', 'dish_id', 'servings'];

    protected function casts(): array
    {
        return ['day' => 'integer', 'servings' => 'integer'];
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
