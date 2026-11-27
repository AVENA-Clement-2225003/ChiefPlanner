<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\IngredientPack;
use App\Models\IngredientSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** "Mon magasin": how the user's own store sells the ingredients they cook with (aisle, packs, prices). */
class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $ingredients = Ingredient::query()->whereIn('id', $this->ingredientIdsInCatalogue($user))->orderBy('name')->get();
        $aisles = IngredientSetting::query()->where('user_id', $user->id)->pluck('aisle_id', 'ingredient_id');
        $packs = IngredientPack::query()->where('user_id', $user->id)->orderBy('size')->get()->groupBy('ingredient_id');

        return response()->json(['data' => $ingredients->map(fn (Ingredient $ingredient) => [
            'ingredientId' => $ingredient->id,
            'name' => $ingredient->name,
            'aisleId' => $aisles[$ingredient->id] ?? null,
            'packs' => $packs->get($ingredient->id, collect())->map(fn (IngredientPack $pack) => $this->presentPack($pack))->values(),
        ])]);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->ingredientIdsInCatalogue($user)->contains($ingredient->id), 403);
        $data = $request->validate([
            'aisleId' => ['nullable', 'exists:aisles,id'],
            'packs' => ['present', 'array', 'max:10'],
            'packs.*.unitId' => ['required', 'exists:units,id'],
            'packs.*.size' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'packs.*.price' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ]);

        DB::transaction(function () use ($user, $ingredient, $data) {
            IngredientSetting::query()->updateOrInsert(
                ['user_id' => $user->id, 'ingredient_id' => $ingredient->id],
                ['aisle_id' => $data['aisleId'] ?? null],
            );
            IngredientPack::query()->where(['user_id' => $user->id, 'ingredient_id' => $ingredient->id])->delete();
            foreach ($data['packs'] as $pack) {
                IngredientPack::create([
                    'user_id' => $user->id,
                    'ingredient_id' => $ingredient->id,
                    'unit_id' => $pack['unitId'],
                    'size' => $pack['size'],
                    'price' => $pack['price'] ?? null,
                ]);
            }
        });

        $packs = IngredientPack::query()->where(['user_id' => $user->id, 'ingredient_id' => $ingredient->id])->orderBy('size')->get();

        return response()->json([
            'ingredientId' => $ingredient->id,
            'name' => $ingredient->name,
            'aisleId' => $data['aisleId'] ?? null,
            'packs' => $packs->map(fn (IngredientPack $pack) => $this->presentPack($pack))->values(),
        ]);
    }

    /** The ingredients of the dishes the user cooks (their own and the ones they adopted). */
    private function ingredientIdsInCatalogue(User $user)
    {
        return DB::table('dish_ingredients')
            ->join('dishes', 'dishes.id', '=', 'dish_ingredients.dish_id')
            ->where(fn ($query) => $query
                ->where('dishes.owner_id', $user->id)
                ->orWhereIn('dishes.id', DB::table('dish_subscriptions')->where('user_id', $user->id)->select('dish_id')))
            ->distinct()
            ->pluck('dish_ingredients.ingredient_id');
    }

    /** @return array<string, mixed> */
    private function presentPack(IngredientPack $pack): array
    {
        return ['unitId' => $pack->unit_id, 'size' => $pack->size, 'price' => $pack->price];
    }
}
