<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DishSubscription extends Model
{
    use HasFactory;
    
    protected $table = 'dish_subscriptions';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'plat_id',
    ];

    public function plat()
    {
        return $this->belongsTo(Plats::class, 'plat_id', 'id_plat');
    }
}
