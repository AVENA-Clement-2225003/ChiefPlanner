<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One ingredient line of a dish; the quantity is always for one person. */
class DishIngredient extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'dish_ingredients';

    protected $primaryKey = null;

    protected $fillable = ['dish_id', 'ingredient_id', 'quantity_per_person', 'unit_id'];

    protected function casts(): array
    {
        return ['quantity_per_person' => 'float'];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
