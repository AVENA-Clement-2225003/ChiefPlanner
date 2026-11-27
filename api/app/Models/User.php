<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['sub', 'email', 'name', 'is_admin'];

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'owner_id');
    }

    protected function casts(): array
    {
        return ['is_admin' => 'boolean'];
    }
}
