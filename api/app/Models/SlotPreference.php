<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotPreference extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = ['user_id', 'day', 'slot', 'enabled', 'servings', 'flow'];

    protected function casts(): array
    {
        return ['day' => 'integer', 'enabled' => 'boolean', 'servings' => 'integer'];
    }
}
