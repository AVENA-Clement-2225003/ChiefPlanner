<?php

namespace App\Catalog;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves a dish from a form where quantities are typed for N people. Quantities are stored for ONE person
 * (ADR 0005): the division happens here and nowhere else.
 */
class DishWriter
{
    private const DECIMALS = 4;

    private const WHOLE_NUMBER_TOLERANCE = 0.0001;

    public function __construct(private readonly IngredientCatalog $ingredients) {}

    /**
     * @param  array<string, mixed>  $data  a validated StoreDishRequest payload
     * @return array{0: Dish, 1: list<array<string, mixed>>} the dish and the non-blocking warnings
     */
    public function save(User $owner, ?Dish $dish, array $data): array
    {
        return DB::transaction(function () use ($owner, $dish, $data) {
            $dish = $this->saveDish($owner, $dish, $data);
            $dish->tags()->sync($data['tagIds'] ?? []);

            [$lines, $warnings] = $this->buildLines($owner, $data);
            $dish->lines()->delete();
            $dish->lines()->createMany($lines);

            $dish->steps()->delete();
            $dish->steps()->createMany($this->buildSteps($data['steps'] ?? []));

            if (! $dish->is_shared) {
                $dish->subscribers()->detach();
            }

            return [$dish->load(['tags', 'lines.ingredient', 'steps']), $warnings];
        });
    }

    /** @param array<string, mixed> $data */
    private function saveDish(User $owner, ?Dish $dish, array $data): Dish
    {
        $attributes = [
            'name' => trim($data['name']),
            'prep_time_minutes' => $data['prepTimeMinutes'] ?? 0,
            'difficulty' => $data['difficulty'] ?? 'easy',
            'slots' => array_values(array_unique($data['slots'])),
            'is_shared' => (bool) ($data['isShared'] ?? false),
        ];

        if ($dish === null) {
            return Dish::create($attributes + ['owner_id' => $owner->id]);
        }

        $dish->update($attributes);

        return $dish;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    private function buildLines(User $owner, array $data): array
    {
        $servings = (int) $data['servings'];
        $units = Unit::query()->get()->keyBy('id');
        $lines = [];
        $warnings = [];

        foreach ($data['ingredients'] as $index => $line) {
            $ingredient = $this->resolveIngredient($owner, $line, $index);
            if (isset($lines[$ingredient->id])) {
                throw ValidationException::withMessages(["ingredients.$index" => "« {$ingredient->name} » apparaît deux fois."]);
            }

            $perPerson = round($line['quantity'] / $servings, self::DECIMALS);
            $lines[$ingredient->id] = [
                'ingredient_id' => $ingredient->id,
                'quantity_per_person' => $perPerson,
                'unit_id' => $line['unitId'],
            ];

            if ($units[$line['unitId']]->isCount() && ! $this->isWholeNumber($perPerson)) {
                $warnings[] = ['ingredientId' => $ingredient->id, 'ingredient' => $ingredient->name, 'quantityPerPerson' => $perPerson];
            }
        }

        return [array_values($lines), $warnings];
    }

    /** @param array<string, mixed> $line */
    private function resolveIngredient(User $owner, array $line, int $index): Ingredient
    {
        if (! empty($line['ingredientId'])) {
            $ingredient = Ingredient::query()->visibleTo($owner)->find($line['ingredientId']);
            if ($ingredient === null) {
                throw ValidationException::withMessages(["ingredients.$index.ingredientId" => 'Ingrédient introuvable.']);
            }

            return $ingredient;
        }

        return $this->ingredients->findOrCreate($owner, $line['ingredientName'])[0];
    }

    /**
     * @param  list<string>  $instructions
     * @return list<array<string, mixed>>
     */
    private function buildSteps(array $instructions): array
    {
        $steps = [];
        foreach (array_values(array_filter(array_map('trim', $instructions))) as $index => $instruction) {
            $steps[] = ['position' => $index + 1, 'instruction' => $instruction];
        }

        return $steps;
    }

    private function isWholeNumber(float $value): bool
    {
        return abs($value - round($value)) <= self::WHOLE_NUMBER_TOLERANCE;
    }
}
