<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quantitees extends Model
{
    use HasFactory;
    protected $table = 'quantitees';
    public $timestamps = false;
    protected $fillable = [
        'id_plat',
        'id_ingredient',
        'quantity',
    ];
}
