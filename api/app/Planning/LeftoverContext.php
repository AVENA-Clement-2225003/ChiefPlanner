<?php

namespace App\Planning;

use App\Models\Dish;
use App\Models\IngredientPack;
use App\Models\Unit;
use Illuminate\Support\Collection;

/** Everything the leftover search reads, loaded once per Proposal. */
final readonly class LeftoverContext
{
    /**
     * @param  Collection<int, Collection<int, IngredientPack>>  $packs  the user's packs per ingredient
     * @param  Collection<string, Unit>  $units
     * @param  Collection<int, Dish>  $dishes  candidate dishes with their ingredient lines
     */
    public function __construct(public Collection $packs, public Collection $units, public Collection $dishes) {}
}
