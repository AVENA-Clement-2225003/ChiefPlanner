<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plats extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $primaryKey = 'id_plat';
    protected $fillable = [
        'nom',
        'id_utilisateur',
    ];
}
