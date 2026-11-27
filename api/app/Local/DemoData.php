<?php

namespace App\Local;

use App\Catalog\DishWriter;
use App\Models\Dish;
use App\Models\MealFlow;
use App\Models\User;

/** A handful of dishes and MealFlows so the screens are not empty when working locally. */
class DemoData
{
    public function __construct(private readonly DishWriter $writer) {}

    public function seedFor(User $user): void
    {
        if (Dish::query()->where('owner_id', $user->id)->exists()) {
            return;
        }

        $dishes = [];
        foreach ($this->dishes() as $name => $data) {
            [$dish] = $this->writer->save($user, null, ['name' => $name, 'isShared' => false] + $data);
            $dishes[$name] = $dish;
        }

        $this->flow($user, 'Semaine végétarienne', [$dishes['Curry de légumes'], $dishes['Soupe de potiron']]);
        $this->flow($user, 'Repas du dimanche', [$dishes['Poulet rôti'], $dishes['Pâtes bolognaise']]);
    }

    /** @param list<Dish> $dishes */
    private function flow(User $user, string $name, array $dishes): void
    {
        MealFlow::create(['owner_id' => $user->id, 'name' => $name])->dishes()->sync(array_map(fn (Dish $dish) => $dish->id, $dishes));
    }

    /** @return array<string, array<string, mixed>> */
    private function dishes(): array
    {
        return [
            'Gnocchis nuggets' => [
                'servings' => 2, 'slots' => ['lunch', 'dinner'], 'tagIds' => ['quick', 'cheap'], 'prepTimeMinutes' => 15,
                'ingredients' => [
                    ['ingredientName' => 'Gnocchis', 'quantity' => 2, 'unitId' => 'portion'],
                    ['ingredientName' => 'Nuggets', 'quantity' => 10, 'unitId' => 'unit'],
                ],
                'steps' => ['Cuire les gnocchis', 'Poêler les nuggets'],
            ],
            'Curry de légumes' => [
                'servings' => 4, 'slots' => ['lunch', 'dinner'], 'tagIds' => ['vegetarian', 'asian'], 'prepTimeMinutes' => 25,
                'ingredients' => [
                    ['ingredientName' => 'Lait de coco', 'quantity' => 400, 'unitId' => 'ml'],
                    ['ingredientName' => 'Riz', 'quantity' => 300, 'unitId' => 'g'],
                    ['ingredientName' => 'Carotte', 'quantity' => 4, 'unitId' => 'unit'],
                ],
                'steps' => ['Faire revenir les légumes', 'Ajouter le lait de coco', 'Servir avec le riz'],
            ],
            'Soupe de potiron' => [
                'servings' => 4, 'slots' => ['dinner'], 'tagIds' => ['vegetarian', 'soup'], 'prepTimeMinutes' => 30,
                'ingredients' => [
                    ['ingredientName' => 'Potiron', 'quantity' => 800, 'unitId' => 'g'],
                    ['ingredientName' => 'Crème fraîche', 'quantity' => 20, 'unitId' => 'cl'],
                ],
                'steps' => ['Cuire le potiron', 'Mixer avec la crème'],
            ],
            'Poulet rôti' => [
                'servings' => 4, 'slots' => ['lunch'], 'tagIds' => ['poultry', 'oven', 'french'], 'prepTimeMinutes' => 75,
                'ingredients' => [
                    ['ingredientName' => 'Poulet entier', 'quantity' => 1, 'unitId' => 'unit'],
                    ['ingredientName' => 'Ail', 'quantity' => 3, 'unitId' => 'clove'],
                ],
                'steps' => ['Préchauffer le four à 200 °C', 'Enfourner 1 h 15'],
            ],
            'Pâtes bolognaise' => [
                'servings' => 4, 'slots' => ['lunch', 'dinner'], 'tagIds' => ['meat', 'italian'], 'prepTimeMinutes' => 40,
                'ingredients' => [
                    ['ingredientName' => 'Pâtes', 'quantity' => 400, 'unitId' => 'g'],
                    ['ingredientName' => 'Bœuf haché', 'quantity' => 400, 'unitId' => 'g'],
                    ['ingredientName' => 'Tomate', 'quantity' => 6, 'unitId' => 'unit'],
                ],
                'steps' => ['Faire revenir la viande', 'Ajouter les tomates', 'Cuire les pâtes'],
            ],
        ];
    }
}
