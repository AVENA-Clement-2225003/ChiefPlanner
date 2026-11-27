<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Moderation of the ingredient names: duplicates are merged by hand. */
class AdminIngredientController extends Controller
{
    public function index(): JsonResponse
    {
        $ingredients = Ingredient::query()->withCount('dishLines')->orderBy('name')->get();

        return response()->json(['data' => $ingredients->map(fn (Ingredient $ingredient) => [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'usedByDishes' => $ingredient->dish_lines_count,
        ])]);
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        if ($ingredient->dishLines()->exists()) {
            return response()->json(['message' => 'Cet ingrédient est utilisé : fusionnez-le avec un autre.'], 409);
        }

        $ingredient->delete();

        return response()->json(null, 204);
    }

    /** Moves every dish line of `$ingredient` onto `intoId`, then removes `$ingredient`. */
    public function merge(Request $request, Ingredient $ingredient): JsonResponse
    {
        $data = $request->validate(['intoId' => ['required', 'integer', 'exists:ingredients,id', 'different:ingredient']]);
        abort_if((int) $data['intoId'] === $ingredient->id, 422, 'On ne fusionne pas un ingrédient avec lui-même.');

        DB::transaction(function () use ($ingredient, $data) {
            $target = (int) $data['intoId'];
            // A dish that already lists the target keeps that line; the duplicate line is dropped.
            // The dish ids are read first: MySQL refuses a delete whose subquery reads the same table (error 1093).
            $alreadyListingTarget = DB::table('dish_ingredients')->where('ingredient_id', $target)->pluck('dish_id');
            DB::table('dish_ingredients')->where('ingredient_id', $ingredient->id)->whereIn('dish_id', $alreadyListingTarget)->delete();
            DB::table('dish_ingredients')->where('ingredient_id', $ingredient->id)->update(['ingredient_id' => $target]);
            $ingredient->delete();
        });

        return response()->json(null, 204);
    }
}
