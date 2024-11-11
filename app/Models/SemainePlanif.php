<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemainePlanif extends Model
{
    use HasFactory;
    protected $table = 'semaine_planif';
    public $timestamps = false;
    protected $fillable = [
        'id_plat',
        'id_jour',
        'id_utilisateur',
    ];
}
