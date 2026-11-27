<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';
}
