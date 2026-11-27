<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** An ingredient is only a name, the same for everybody. What is personal (packs, prices, aisle) lives in the user's store settings. */
class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_key'];

    /** "  Crème  Fraîche " and "creme fraiche" are the same ingredient. */
    public static function keyFor(string $name): string
    {
        return Str::lower(Str::squish(Str::ascii($name)));
    }

    public function dishLines(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }
}
