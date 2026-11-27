<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DishStep extends Model
{
    public $timestamps = false;

    protected $fillable = ['dish_id', 'position', 'instruction'];
}
