<?php

namespace App\Http\Resources;

use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Dish */
class DishResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'prepTimeMinutes' => $this->prep_time_minutes,
            'difficulty' => $this->difficulty,
            'slots' => $this->slots,
            'tagIds' => $this->tags->pluck('id')->values(),
            'isShared' => $this->is_shared,
            'ownedByCurrentUser' => $this->isOwnedBy($user),
            'subscribed' => $this->subscribers->contains($user),
            'ingredients' => $this->lines->map(fn ($line) => [
                'ingredientId' => $line->ingredient_id,
                'name' => $line->ingredient->name,
                'quantityPerPerson' => $line->quantity_per_person,
                'unitId' => $line->unit_id,
            ])->values(),
            'steps' => $this->steps->map(fn ($step) => [
                'id' => $step->id,
                'position' => $step->position,
                'instruction' => $step->instruction,
            ])->values(),
        ];
    }
}
