<?php

namespace App\Http\Controllers;

use App\Catalog\IngredientCatalog;
use App\Models\Ingredient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    private const SEARCH_LIMIT = 50;

    public function __construct(private readonly IngredientCatalog $catalog) {}

    public function index(Request $request): JsonResponse
    {
        $search = Ingredient::keyFor((string) $request->query('q', ''));

        $ingredients = Ingredient::query()
            ->when($search !== '', fn ($query) => $query->where('name_key', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->limit(self::SEARCH_LIMIT)
            ->get();

        return response()->json(['data' => $ingredients->map(fn (Ingredient $ingredient) => $this->present($ingredient))]);
    }

    /** Creates the ingredient unless one with the same name exists, in which case that one is returned. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        [$ingredient, $created] = $this->catalog->findOrCreate($data['name']);

        return response()->json($this->present($ingredient), $created ? 201 : 200);
    }

    /** @return array<string, mixed> */
    private function present(Ingredient $ingredient): array
    {
        return ['id' => $ingredient->id, 'name' => $ingredient->name];
    }
}
