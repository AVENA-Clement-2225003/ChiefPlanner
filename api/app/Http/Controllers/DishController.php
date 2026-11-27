<?php

namespace App\Http\Controllers;

use App\Catalog\DishWriter;
use App\Http\Requests\DishRequest;
use App\Http\Resources\DishResource;
use App\Models\Dish;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DishController extends Controller
{
    private const WITH = ['tags', 'lines.ingredient', 'steps', 'subscribers'];

    public function __construct(private readonly DishWriter $writer) {}

    /** The user's own catalogue: dishes they created and dishes they adopted. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $dishes = Dish::query()->inCatalogueOf($request->user())->with(self::WITH)->orderBy('name')->get();

        return DishResource::collection($dishes);
    }

    /** What the Community shares, minus the user's own dishes. */
    public function shared(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('q', ''));
        $slot = $request->query('slot');
        $tag = $request->query('tag');

        $dishes = Dish::query()
            ->where('is_shared', true)
            ->where('owner_id', '!=', $request->user()->id)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($slot, fn ($query) => $query->whereJsonContains('slots', $slot))
            ->when($tag, fn ($query) => $query->whereHas('tags', fn ($tags) => $tags->whereKey($tag)))
            ->with(self::WITH)
            ->orderBy('name')
            ->get();

        return DishResource::collection($dishes);
    }

    public function show(Dish $dish): DishResource
    {
        $this->authorize('view', $dish);

        return new DishResource($dish->load(self::WITH));
    }

    public function store(DishRequest $request): JsonResponse
    {
        [$dish, $warnings] = $this->writer->save($request->user(), null, $request->validated());

        return $this->saved($dish, $warnings, $request, 201);
    }

    public function update(DishRequest $request, Dish $dish): JsonResponse
    {
        $this->authorize('manage', $dish);
        [$dish, $warnings] = $this->writer->save($request->user(), $dish, $request->validated());

        return $this->saved($dish, $warnings, $request, 200);
    }

    public function destroy(Dish $dish): JsonResponse
    {
        $this->authorize('manage', $dish);
        $dish->delete();

        return response()->json(null, 204);
    }

    /** Sharing off removes the dish from everybody who adopted it (no copy is kept, RGPD). */
    public function share(Request $request, Dish $dish): DishResource
    {
        $this->authorize('manage', $dish);
        $data = $request->validate(['isShared' => ['required', 'boolean']]);

        $dish->update(['is_shared' => $data['isShared']]);
        if (! $dish->is_shared) {
            $dish->releaseSubscribers();
        }

        return new DishResource($dish->load(self::WITH));
    }

    public function subscribe(Request $request, Dish $dish): DishResource
    {
        $this->authorize('subscribe', $dish);
        $dish->subscribers()->syncWithoutDetaching([$request->user()->id]);

        return new DishResource($dish->load(self::WITH));
    }

    public function unsubscribe(Request $request, Dish $dish): DishResource
    {
        $dish->releaseSubscriber($request->user());

        return new DishResource($dish->load(self::WITH));
    }

    /** @param list<array<string, mixed>> $warnings */
    private function saved(Dish $dish, array $warnings, Request $request, int $status): JsonResponse
    {
        $dish->load('subscribers');

        return response()->json([
            'dish' => (new DishResource($dish))->toArray($request),
            'warnings' => $warnings,
        ], $status);
    }
}
