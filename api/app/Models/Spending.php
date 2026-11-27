<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Spending extends Model
{
    protected $fillable = ['user_id', 'amount', 'spent_on'];

    protected function casts(): array
    {
        return ['amount' => 'float', 'spent_on' => 'date'];
    }
}
