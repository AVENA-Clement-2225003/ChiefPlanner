<?php

namespace App\Shopping;

use App\Models\Extra;
use App\Models\IngredientPack;
use App\Models\IngredientSetting;
use App\Models\Meal;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Builds the shopping list of the current Week: needs per ingredient, packs to buy, leftovers, extras. */
class ShoppingListService
{
    private const WHOLE_NUMBER_TOLERANCE = 0.0001;

    private const DISPLAY_DECIMALS = 2;

    public function __construct(private readonly WeeklyNeeds $needs, private readonly PackPlanner $planner) {}

    /** @return array{items: list<array<string, mixed>>, extras: list<array<string, mixed>>, estimatedTotal: float, unpricedItems: int} */
    public function build(User $user): array
    {
        $meals = Meal::query()->where('user_id', $user->id)->with('dish.lines.ingredient')->get();
        $units = Unit::query()->get()->keyBy('id');
        $aisles = IngredientSetting::query()->where('user_id', $user->id)->pluck('aisle_id', 'ingredient_id');
        $packs = IngredientPack::query()->where('user_id', $user->id)->get()->groupBy('ingredient_id');
        $ticked = DB::table('shopping_ticks')->where('user_id', $user->id)->pluck('item_key')->flip();

        $items = [];
        foreach ($this->needs->compute($meals) as $key => $need) {
            $items[] = $this->presentItem($need, $units, $packs->get($need->ingredientId, collect()), $aisles[$need->ingredientId] ?? null)
                + ['purchased' => isset($ticked[$key])];
        }
        usort($items, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));

        return [
            'items' => $items,
            'extras' => Extra::query()->where('user_id', $user->id)->orderBy('label')->get()
                ->map(fn (Extra $extra) => ['id' => $extra->id, 'label' => $extra->label, 'aisleId' => $extra->aisle_id])->all(),
            'estimatedTotal' => round(array_sum(array_column($items, 'estimatedPrice')), 2),
            'unpricedItems' => count(array_filter($items, fn (array $item) => $item['estimatedPrice'] === null)),
        ];
    }

    /**
     * @param  Collection<string, Unit>  $units
     * @param  Collection<int, IngredientPack>  $packs  the user's packs for this ingredient, any unit
     * @return array<string, mixed>
     */
    private function presentItem(Need $need, Collection $units, Collection $packs, ?string $aisleId): array
    {
        [$quantity, $unitId] = $this->display($need->family, $need->countUnitId, $need->base);
        $item = [
            'key' => $need->key(),
            'ingredientId' => $need->ingredientId,
            'name' => $need->ingredientName,
            'aisleId' => $aisleId,
            'quantity' => $quantity,
            'unitId' => $unitId,
            'packs' => null,
            'estimatedPrice' => null,
        ];

        $usable = $packs->filter(fn (IngredientPack $pack) => $this->fits($need, $units[$pack->unit_id]))->values();
        $plan = $usable->isEmpty() ? null : $this->planner->plan($need->base, $usable->map(fn ($pack) => $pack->size * $units[$pack->unit_id]->factor)->all());
        if ($plan === null) {
            return $item;
        }

        $lines = [];
        $price = 0.0;
        $allPriced = true;
        foreach ($usable as $index => $pack) {
            $count = $plan['counts'][$index];
            if ($count === 0) {
                continue;
            }
            $lines[] = ['unitId' => $pack->unit_id, 'size' => $pack->size, 'count' => $count, 'price' => $pack->price];
            $allPriced = $allPriced && $pack->price !== null;
            $price += $count * ($pack->price ?? 0);
        }

        [$leftover, $leftoverUnit] = $this->display($need->family, $need->countUnitId, $plan['leftover']);
        $item['packs'] = ['lines' => $lines, 'leftover' => ['quantity' => $leftover, 'unitId' => $leftoverUnit]];
        $item['estimatedPrice'] = $allPriced ? round($price, 2) : null;

        return $item;
    }

    /** A pack fits a need when it is in the same family, and for counts in the very same unit. */
    private function fits(Need $need, Unit $packUnit): bool
    {
        return $packUnit->family === $need->family && ($need->countUnitId === null || $packUnit->id === $need->countUnitId);
    }

    /** @return array{0: float|int, 1: string} the quantity and unit to show for an amount in base units */
    private function display(string $family, ?string $countUnitId, float $base): array
    {
        if ($family === 'count') {
            return [(int) ceil($base - self::WHOLE_NUMBER_TOLERANCE), $countUnitId];
        }

        $unit = ['mass' => ['g', 'kg'], 'volume' => ['ml', 'l']][$family];

        return $base >= 1000
            ? [round($base / 1000, self::DISPLAY_DECIMALS), $unit[1]]
            : [round($base, self::DISPLAY_DECIMALS), $unit[0]];
    }
}
