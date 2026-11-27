<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A shopping list item that has nothing to do with a dish (cereals, toilet paper...). */
class Extra extends Model
{
    protected $fillable = ['user_id', 'label', 'aisle_id'];
}
