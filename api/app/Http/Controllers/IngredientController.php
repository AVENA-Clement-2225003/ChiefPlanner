<?php

namespace App\Http\Controllers;

use App\Catalog\IngredientCatalog;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function __construct(private readonly IngredientCatalog $catalog) {}

    public function index(Request $request): JsonResponse
    {
        $search = Ingredient::keyFor((string) $request->query('q', ''));

        $ingredients = Ingredient::query()
            ->visibleTo($request->user())
            ->when($search !== '', fn ($query) => $query->where('name_key', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json(['data' => $ingredients->map(fn (Ingredient $ingredient) => $this->present($ingredient, $request))]);
    }

    /** Creates the ingredient unless one with the same name is already visible, in which case that one is returned. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'isShared' => ['nullable', 'boolean'],
        ]);

        [$ingredient, $created] = $this->catalog->findOrCreate($request->user(), $data['name'], $data['isShared'] ?? true);

        return response()->json($this->present($ingredient, $request), $created ? 201 : 200);
    }

    public function update(Request $request, Ingredient $ingredient): JsonResponse
    {
        $this->authorize('manage', $ingredient);
        $data = $request->validate(['isShared' => ['required', 'boolean']]);

        $ingredient->update(['is_shared' => $data['isShared']]);

        return response()->json($this->present($ingredient, $request));
    }

    public function destroy(Ingredient $ingredient): JsonResponse
    {
        $this->authorize('manage', $ingredient);
        if ($ingredient->dishLines()->exists()) {
            return response()->json(['message' => 'Cet ingrédient est utilisé par au moins un plat.'], 409);
        }

        $ingredient->delete();

        return response()->json(null, 204);
    }

    /** @return array<string, mixed> */
    private function present(Ingredient $ingredient, Request $request): array
    {
        return [
            'id' => $ingredient->id,
            'name' => $ingredient->name,
            'isShared' => $ingredient->is_shared,
            'ownedByCurrentUser' => $ingredient->owner_id === $request->user()->id,
        ];
    }
}
