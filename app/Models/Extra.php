<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'extra';
    public $primaryKey = ['intitule', 'id_utilisateur'];
    public $incrementing = false;
    protected $fillable = [
        'quantite',
        'id_utilisateur',
    ];
}
