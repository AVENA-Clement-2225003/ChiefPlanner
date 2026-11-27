<?php

namespace App\Planning;

use App\Models\MealFlow;
use App\Models\Preferences;
use App\Models\SlotPreference;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreferencesService
{
    public const DAYS = [0, 1, 2, 3, 4, 5, 6];

    public const SLOTS = ['lunch', 'dinner'];

    /** @return array{mealFlowAId: int|null, mealFlowBId: int|null, slots: list<array<string, mixed>>} */
    public function forUser(User $user): array
    {
        $preferences = Preferences::find($user->id);
        $saved = SlotPreference::query()->where('user_id', $user->id)->get()->keyBy(fn ($row) => $row->day.':'.$row->slot);

        $slots = [];
        foreach (self::DAYS as $day) {
            foreach (self::SLOTS as $slot) {
                $row = $saved->get($day.':'.$slot);
                $slots[] = [
                    'day' => $day,
                    'slot' => $slot,
                    'enabled' => $row?->enabled ?? true,
                    'servings' => $row?->servings ?? 1,
                    'flow' => $row?->flow,
                ];
            }
        }

        return [
            'mealFlowAId' => $preferences?->meal_flow_a_id,
            'mealFlowBId' => $preferences?->meal_flow_b_id,
            'slots' => $slots,
        ];
    }

    /** @param array<string, mixed> $data a validated preferences payload */
    public function save(User $user, array $data): void
    {
        $flowIds = array_filter([$data['mealFlowAId'] ?? null, $data['mealFlowBId'] ?? null]);
        $owned = MealFlow::query()->where('owner_id', $user->id)->whereIn('id', $flowIds)->count();
        if ($owned !== count($flowIds)) {
            throw ValidationException::withMessages(['mealFlowAId' => 'Ce MealFlow ne vous appartient pas.']);
        }

        DB::transaction(function () use ($user, $data) {
            Preferences::updateOrCreate(
                ['user_id' => $user->id],
                ['meal_flow_a_id' => $data['mealFlowAId'] ?? null, 'meal_flow_b_id' => $data['mealFlowBId'] ?? null],
            );

            SlotPreference::query()->where('user_id', $user->id)->delete();
            SlotPreference::query()->insert(array_map(fn (array $slot) => [
                'user_id' => $user->id,
                'day' => $slot['day'],
                'slot' => $slot['slot'],
                'enabled' => $slot['enabled'],
                'servings' => $slot['servings'],
                'flow' => $slot['flow'] ?? null,
            ], $data['slots']));
        });
    }
}
