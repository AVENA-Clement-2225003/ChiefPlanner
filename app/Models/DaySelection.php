<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaySelection extends Model
{
    use HasFactory;
    protected $table = 'day_selected';
    public $timestamps = false;
    protected $fillable = [
        'id_utilisateur',
        'id_jour',
    ];
}
