<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** A dish typed for `servings` people; the quantities are divided down to one person on save (ADR 0005). */
class DishRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'prepTimeMinutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*' => ['in:lunch,dinner'],
            'tagIds' => ['nullable', 'array'],
            'tagIds.*' => ['exists:tags,id'],
            'isShared' => ['nullable', 'boolean'],
            'servings' => ['required', 'integer', 'min:1', 'max:100'],
            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.ingredientId' => ['nullable', 'integer'],
            'ingredients.*.ingredientName' => ['required_without:ingredients.*.ingredientId', 'nullable', 'string', 'max:120'],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'ingredients.*.unitId' => ['required', 'exists:units,id'],
            'steps' => ['nullable', 'array'],
            'steps.*' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
