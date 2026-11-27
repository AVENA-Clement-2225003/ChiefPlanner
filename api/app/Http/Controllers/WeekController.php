<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Meal;
use App\Models\User;
use App\Planning\WeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WeekController extends Controller
{
    public function __construct(private readonly WeekService $week) {}

    public function show(Request $request): JsonResponse
    {
        return $this->weekResponse($request->user());
    }

    /** The "refresh" button: a new Proposal for the whole Week. */
    public function propose(Request $request): JsonResponse
    {
        $this->week->propose($request->user());

        return $this->weekResponse($request->user());
    }

    public function revert(Request $request): JsonResponse
    {
        if (! $this->week->revert($request->user())) {
            return response()->json(['message' => 'Aucune Proposition précédente à retrouver.'], 409);
        }

        return $this->weekResponse($request->user());
    }

    public function restore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meals' => ['required', 'array', 'max:14'],
            'meals.*.day' => ['required', 'integer', 'between:0,6'],
            'meals.*.slot' => ['required', 'in:lunch,dinner'],
            'meals.*.dishId' => ['nullable', 'integer'],
            'meals.*.servings' => ['required', 'integer', 'between:1,100'],
        ]);

        $this->week->restore($request->user(), $data['meals']);

        return $this->weekResponse($request->user());
    }

    public function updateMeal(Request $request, int $day, string $slot): JsonResponse
    {
        $data = $request->validate([
            'dishId' => ['nullable', 'integer'],
            'servings' => ['sometimes', 'integer', 'between:1,100'],
        ]);
        $this->assertDishInCatalogue($request->user(), $data['dishId'] ?? null);

        return response()->json($this->presentMeal($this->week->updateMeal($request->user(), $day, $slot, $data)));
    }

    private function assertDishInCatalogue(User $user, ?int $dishId): void
    {
        if ($dishId !== null && ! Dish::query()->inCatalogueOf($user)->whereKey($dishId)->exists()) {
            throw ValidationException::withMessages(['dishId' => "Ce plat n'est pas dans votre catalogue."]);
        }
    }

    private function weekResponse(User $user): JsonResponse
    {
        return response()->json([
            'meals' => $this->week->meals($user)->map(fn (Meal $meal) => $this->presentMeal($meal))->values(),
            'hasPrevious' => $this->week->hasPrevious($user),
        ]);
    }

    /** @return array<string, mixed> */
    private function presentMeal(Meal $meal): array
    {
        return [
            'day' => $meal->day,
            'slot' => $meal->slot,
            'dishId' => $meal->dish_id,
            'dishName' => $meal->dish?->name,
            'servings' => $meal->servings,
            'needsReplacement' => $meal->dish_id === null,
        ];
    }
}
