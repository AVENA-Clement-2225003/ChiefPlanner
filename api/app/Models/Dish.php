<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'prep_time_minutes', 'difficulty', 'slots', 'is_shared'];

    protected function casts(): array
    {
        return ['slots' => 'array', 'is_shared' => 'boolean', 'prep_time_minutes' => 'integer'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'dish_tag');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DishIngredient::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(DishStep::class)->orderBy('position');
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dish_subscriptions');
    }

    /**
     * Takes the dish out of the catalogue of everybody who adopted it, and out of their MealFlows.
     * No copy is kept: this is what makes unsharing and deleting a dish a real removal (RGPD).
     */
    public function releaseSubscribers(): void
    {
        $this->releaseUsers($this->subscribers()->pluck('users.id')->all());
    }

    public function releaseSubscriber(User $user): void
    {
        $this->releaseUsers([$user->id]);
    }

    /** @param list<int> $userIds */
    private function releaseUsers(array $userIds): void
    {
        $flowIds = MealFlow::query()->whereIn('owner_id', $userIds)->select('id');
        DB::table('meal_flow_dish')->where('dish_id', $this->id)->whereIn('meal_flow_id', $flowIds)->delete();
        $this->subscribers()->detach($userIds);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /** The dishes of a user's own catalogue: created by them, or adopted from the Community. */
    public function scopeInCatalogueOf(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $own) => $own
            ->where('owner_id', $user->id)
            ->orWhereHas('subscribers', fn (Builder $subscribers) => $subscribers->whereKey($user->id)));
    }
}
