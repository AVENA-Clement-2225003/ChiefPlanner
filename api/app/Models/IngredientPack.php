<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A quantity a user can buy an ingredient in at their store, with an optional price. */
class IngredientPack extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'ingredient_id', 'unit_id', 'size', 'price'];

    protected function casts(): array
    {
        return ['size' => 'float', 'price' => 'float'];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
