<?php

namespace App\Planning;

use App\Models\Dish;
use App\Models\IngredientPack;
use App\Models\Meal;
use App\Models\Unit;
use App\Models\User;
use App\Shopping\PackPlanner;
use App\Shopping\WeeklyNeeds;
use Illuminate\Support\Collection;

/**
 * Starts from a random Proposal, then swaps dishes (repeating one when it helps) while that lowers the Leftover
 * of the packs the user buys. A dish is never used more than MAX_REPEATS times in the week (ADR 0005).
 */
class LeftoverAwareSelection implements SelectionStrategy
{
    public const MAX_REPEATS = 2;

    private const PASSES = 2;

    private const MIN_IMPROVEMENT = 0.0001;

    public function __construct(
        private readonly RandomSelection $base,
        private readonly WeeklyNeeds $needs,
        private readonly PackPlanner $planner,
    ) {}

    public function select(User $user, array $requests): array
    {
        $chosen = $this->base->select($user, $requests);
        $packs = IngredientPack::query()->where('user_id', $user->id)->get()->groupBy('ingredient_id');
        if ($packs->isEmpty()) {
            return $chosen;
        }

        $context = new LeftoverContext(
            $packs,
            Unit::query()->get()->keyBy('id'),
            Dish::query()->with('lines.ingredient')->whereIn('id', array_unique(array_merge(...array_map(fn (SlotRequest $r) => $r->candidateDishIds, $requests) ?: [[]])))->get()->keyBy('id'),
        );

        for ($pass = 0; $pass < self::PASSES; $pass++) {
            foreach ($requests as $request) {
                $chosen[$request->key()] = $this->bestDishFor($request, $requests, $chosen, $context);
            }
        }

        return $chosen;
    }

    /**
     * @param  list<SlotRequest>  $requests
     * @param  array<string, int|null>  $chosen
     */
    private function bestDishFor(SlotRequest $request, array $requests, array $chosen, LeftoverContext $context): ?int
    {
        $current = $chosen[$request->key()];
        $bestDish = $current;
        $bestWaste = $this->waste($requests, $chosen, $context);

        foreach ($request->candidateDishIds as $candidate) {
            if ($candidate === $current || $this->timesUsed($chosen, $candidate) >= self::MAX_REPEATS) {
                continue;
            }
            $waste = $this->waste($requests, [$request->key() => $candidate] + $chosen, $context);
            if ($waste < $bestWaste - self::MIN_IMPROVEMENT) {
                [$bestDish, $bestWaste] = [$candidate, $waste];
            }
        }

        return $bestDish;
    }

    /** @param array<string, int|null> $chosen */
    private function timesUsed(array $chosen, int $dishId): int
    {
        return count(array_filter($chosen, fn (?int $id) => $id === $dishId));
    }

    /**
     * The share of what the user would buy that goes unused, added up over the ingredients they have packs for.
     *
     * @param  list<SlotRequest>  $requests
     * @param  array<string, int|null>  $chosen
     */
    private function waste(array $requests, array $chosen, LeftoverContext $context): float
    {
        $meals = new Collection;
        foreach ($requests as $request) {
            $dish = isset($chosen[$request->key()]) ? $context->dishes->get($chosen[$request->key()]) : null;
            $meals->push((new Meal(['servings' => $request->servings]))->setRelation('dish', $dish));
        }

        $waste = 0.0;
        foreach ($this->needs->compute($meals) as $need) {
            $usable = $context->packs->get($need->ingredientId, collect())->filter(function (IngredientPack $pack) use ($need, $context) {
                $unit = $context->units[$pack->unit_id];

                return $unit->family === $need->family && ($need->countUnitId === null || $unit->id === $need->countUnitId);
            })->values();
            if ($usable->isEmpty()) {
                continue;
            }

            $plan = $this->planner->plan($need->base, $usable->map(fn ($pack) => $pack->size * $context->units[$pack->unit_id]->factor)->all());
            $waste += $plan === null ? 0.0 : $plan['leftover'] / $plan['bought'];
        }

        return $waste;
    }
}
