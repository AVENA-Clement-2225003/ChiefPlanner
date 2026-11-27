<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Per-user settings of an ingredient: in which aisle of their store it is. */
class IngredientSetting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = ['user_id', 'ingredient_id', 'aisle_id'];
}
