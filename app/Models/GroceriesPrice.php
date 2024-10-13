<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroceriesPrice extends Model
{
    use HasFactory;
    protected $table = 'groceries_price';
    public $timestamps = false;
    protected $fillable = [
        'price',
    ];
}
