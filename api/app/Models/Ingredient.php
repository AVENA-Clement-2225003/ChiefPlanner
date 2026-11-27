<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'name_key', 'is_shared'];

    protected function casts(): array
    {
        return ['is_shared' => 'boolean'];
    }

    /** "  Crème  Fraîche " and "creme fraiche" are the same ingredient. */
    public static function keyFor(string $name): string
    {
        return Str::lower(Str::squish(Str::ascii($name)));
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function dishLines(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }

    /** Ingredients a user can pick: the ones they created and the ones shared with the Community. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $visible) => $visible->where('owner_id', $user->id)->orWhere('is_shared', true));
    }
}
