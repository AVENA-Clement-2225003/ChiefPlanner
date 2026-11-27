<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['sub', 'email', 'name', 'is_admin'];

    protected function casts(): array
    {
        return ['is_admin' => 'boolean'];
    }
}
