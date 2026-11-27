<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\MealFlow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MealFlowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $flows = MealFlow::query()->where('owner_id', $request->user()->id)->with('dishes:id')->orderBy('name')->get();

        return response()->json(['data' => $flows->map(fn (MealFlow $flow) => $this->present($flow))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);

        $flow = MealFlow::create(['owner_id' => $request->user()->id, 'name' => trim($data['name'])]);

        return response()->json($this->present($flow->load('dishes:id')), 201);
    }

    public function update(Request $request, MealFlow $mealFlow): JsonResponse
    {
        $this->authorize('manage', $mealFlow);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:80'],
            'dishIds' => ['sometimes', 'array'],
            'dishIds.*' => ['integer'],
        ]);

        if (isset($data['name'])) {
            $mealFlow->update(['name' => trim($data['name'])]);
        }
        if (isset($data['dishIds'])) {
            $mealFlow->dishes()->sync($this->dishesFromCatalogue($request, $data['dishIds']));
        }

        return response()->json($this->present($mealFlow->load('dishes:id')));
    }

    public function destroy(MealFlow $mealFlow): JsonResponse
    {
        $this->authorize('manage', $mealFlow);
        $mealFlow->delete();

        return response()->json(null, 204);
    }

    /**
     * A MealFlow only holds dishes of the user's own catalogue.
     *
     * @param  list<int>  $dishIds
     * @return list<int>
     */
    private function dishesFromCatalogue(Request $request, array $dishIds): array
    {
        $allowed = Dish::query()->inCatalogueOf($request->user())->whereIn('id', $dishIds)->pluck('id')->all();
        if (count($allowed) !== count(array_unique($dishIds))) {
            throw ValidationException::withMessages(['dishIds' => 'Certains plats ne sont pas dans votre catalogue.']);
        }

        return $allowed;
    }

    /** @return array<string, mixed> */
    private function present(MealFlow $flow): array
    {
        return ['id' => $flow->id, 'name' => $flow->name, 'dishIds' => $flow->dishes->pluck('id')->values()];
    }
}
