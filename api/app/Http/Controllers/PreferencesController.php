<?php

namespace App\Http\Controllers;

use App\Planning\PreferencesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    public function __construct(private readonly PreferencesService $preferences) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->preferences->forUser($request->user()));
    }

    /** Replaces all preferences at once: the settings screen always sends the whole grid. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mealFlowAId' => ['nullable', 'integer'],
            'mealFlowBId' => ['nullable', 'integer', 'different:mealFlowAId'],
            'slots' => ['required', 'array', 'size:14'],
            'slots.*.day' => ['required', 'integer', 'between:0,6'],
            'slots.*.slot' => ['required', 'in:lunch,dinner'],
            'slots.*.enabled' => ['required', 'boolean'],
            'slots.*.servings' => ['required', 'integer', 'between:1,100'],
            'slots.*.flow' => ['nullable', 'in:a,b'],
        ]);

        $this->preferences->save($request->user(), $data);

        return response()->json($this->preferences->forUser($request->user()));
    }
}
