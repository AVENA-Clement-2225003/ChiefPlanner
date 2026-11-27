<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return ['factor' => 'float'];
    }

    public function isCount(): bool
    {
        return $this->family === 'count';
    }
}
