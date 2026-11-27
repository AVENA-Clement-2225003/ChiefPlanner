<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preferences extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'meal_flow_a_id', 'meal_flow_b_id'];
}
