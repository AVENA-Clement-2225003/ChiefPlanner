<?php

namespace App\Planning;

use App\Models\Dish;
use App\Models\Meal;
use App\Models\Preferences;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** The current Week of a user: Proposals, undo, and edits of single meals. */
class WeekService
{
    public function __construct(private readonly SelectionStrategy $strategy, private readonly PreferencesService $preferences) {}

    /** @return Collection<int, Meal> */
    public function meals(User $user): Collection
    {
        return Meal::query()->where('user_id', $user->id)->with('dish:id,name')->orderBy('day')->orderBy('slot')->get();
    }

    public function hasPrevious(User $user): bool
    {
        return DB::table('previous_weeks')->where('user_id', $user->id)->exists();
    }

    /** Replaces the whole Week for the enabled Slots, keeping the old Week one step back to undo. */
    public function propose(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->rememberCurrentWeek($user);

            $slotRequests = $this->slotRequests($user);
            $chosen = $this->strategy->select($slotRequests);
            $requests = collect($slotRequests)->keyBy(fn (SlotRequest $request) => $request->key());

            Meal::query()->where('user_id', $user->id)->delete();
            Meal::query()->insert($requests->map(fn (SlotRequest $request, string $key) => [
                'user_id' => $user->id,
                'day' => $request->day,
                'slot' => $request->slot,
                'dish_id' => $chosen[$key] ?? null,
                'servings' => $request->servings,
            ])->values()->all());
        });
    }

    /** Goes back to the Week before the last Proposal. Returns false when there is nothing to go back to. */
    public function revert(User $user): bool
    {
        $previous = DB::table('previous_weeks')->where('user_id', $user->id)->first();
        if ($previous === null) {
            return false;
        }

        DB::transaction(function () use ($user, $previous) {
            $this->replaceMeals($user, json_decode($previous->meals, true));
            DB::table('previous_weeks')->where('user_id', $user->id)->delete();
        });

        return true;
    }

    /**
     * Puts back a whole Week sent by the browser (its in-session history goes further back than the one step kept here).
     *
     * @param  list<array<string, mixed>>  $meals
     */
    public function restore(User $user, array $meals): void
    {
        DB::transaction(function () use ($user, $meals) {
            $this->rememberCurrentWeek($user);
            $this->replaceMeals($user, $meals);
        });
    }

    /**
     * Changes one meal: its dish, its servings, or both. A change is saved straight away.
     *
     * @param  array{dishId?: int|null, servings?: int}  $changes
     */
    public function updateMeal(User $user, int $day, string $slot, array $changes): Meal
    {
        $meal = Meal::query()->where(['user_id' => $user->id, 'day' => $day, 'slot' => $slot])->first();
        $attributes = ['servings' => $changes['servings'] ?? $meal?->servings ?? 1];
        if (array_key_exists('dishId', $changes)) {
            $attributes['dish_id'] = $changes['dishId'];
        }

        Meal::query()->updateOrInsert(['user_id' => $user->id, 'day' => $day, 'slot' => $slot], $attributes + ['dish_id' => $meal?->dish_id]);

        return Meal::query()->where(['user_id' => $user->id, 'day' => $day, 'slot' => $slot])->with('dish:id,name')->firstOrFail();
    }

    /** @return list<SlotRequest> */
    private function slotRequests(User $user): array
    {
        $prefs = $this->preferences->forUser($user);
        $catalogue = Dish::query()->inCatalogueOf($user)->get(['id', 'slots']);
        $flowDishIds = $this->flowDishIds($user);

        $requests = [];
        foreach ($prefs['slots'] as $slot) {
            if (! $slot['enabled']) {
                continue;
            }
            $eligible = $catalogue->filter(fn (Dish $dish) => in_array($slot['slot'], $dish->slots, true))->pluck('id')->all();
            $inFlow = isset($flowDishIds[$slot['flow']]) ? array_values(array_intersect($eligible, $flowDishIds[$slot['flow']])) : [];

            // A MealFlow with nothing for this Slot must not leave it empty: fall back to the whole catalogue.
            $requests[] = new SlotRequest($slot['day'], $slot['slot'], $slot['servings'], $inFlow !== [] ? $inFlow : array_values($eligible));
        }

        return $requests;
    }

    /** @return array<string, list<int>> dish ids of MealFlow `a` and `b` */
    private function flowDishIds(User $user): array
    {
        $preferences = Preferences::find($user->id);
        $result = [];
        foreach (['a' => $preferences?->meal_flow_a_id, 'b' => $preferences?->meal_flow_b_id] as $name => $flowId) {
            if ($flowId !== null) {
                $result[$name] = DB::table('meal_flow_dish')->where('meal_flow_id', $flowId)->pluck('dish_id')->all();
            }
        }

        return $result;
    }

    private function rememberCurrentWeek(User $user): void
    {
        $current = Meal::query()->where('user_id', $user->id)->get(['day', 'slot', 'dish_id', 'servings']);
        if ($current->isEmpty()) {
            DB::table('previous_weeks')->where('user_id', $user->id)->delete();

            return;
        }

        $snapshot = $current->map(fn (Meal $meal) => [
            'day' => $meal->day, 'slot' => $meal->slot, 'dishId' => $meal->dish_id, 'servings' => $meal->servings,
        ])->values()->toJson();

        DB::table('previous_weeks')->upsert([['user_id' => $user->id, 'meals' => $snapshot, 'created_at' => now()]], ['user_id'], ['meals', 'created_at']);
    }

    /** @param list<array<string, mixed>> $meals */
    private function replaceMeals(User $user, array $meals): void
    {
        $inCatalogue = Dish::query()->inCatalogueOf($user)->pluck('id')->flip();

        Meal::query()->where('user_id', $user->id)->delete();
        Meal::query()->insert(array_map(fn (array $meal) => [
            'user_id' => $user->id,
            'day' => $meal['day'],
            'slot' => $meal['slot'],
            // A dish that left the catalogue since is no longer there: the meal is kept, to replace.
            'dish_id' => isset($meal['dishId'], $inCatalogue[$meal['dishId']]) ? $meal['dishId'] : null,
            'servings' => $meal['servings'],
        ], $meals));
    }
}
