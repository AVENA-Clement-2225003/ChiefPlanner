<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    protected $table = 'utilisateurs';
    public $timestamps = false;
    public $primaryKey = 'id_utilisateur';
    protected $fillable = [
        'nom',
        'email',
        'password',
    ];
}
