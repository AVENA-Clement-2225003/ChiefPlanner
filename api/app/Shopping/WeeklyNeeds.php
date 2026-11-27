<?php

namespace App\Shopping;

use App\Models\Meal;
use App\Models\Unit;
use Illuminate\Support\Collection;

/** Adds up what a list of meals needs, per ingredient, merging quantities of the same kind (ADR 0005). */
class WeeklyNeeds
{
    /**
     * @param  Collection<int, Meal>  $meals  with `dish.lines.ingredient` loaded; meals without a dish are skipped
     * @return array<string, Need> keyed by `Need::key()`
     */
    public function compute(Collection $meals): array
    {
        $units = Unit::query()->get()->keyBy('id');
        $totals = [];

        foreach ($meals as $meal) {
            foreach ($meal->dish?->lines ?? [] as $line) {
                $unit = $units[$line->unit_id];
                $need = new Need(
                    $line->ingredient_id,
                    $line->ingredient->name,
                    $unit->family,
                    $unit->isCount() ? $unit->id : null,
                    $line->quantity_per_person * $meal->servings * $unit->factor,
                );
                $totals[$need->key()] = isset($totals[$need->key()])
                    ? new Need($need->ingredientId, $need->ingredientName, $need->family, $need->countUnitId, $totals[$need->key()]->base + $need->base)
                    : $need;
            }
        }

        return $totals;
    }
}
