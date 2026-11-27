<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtraController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $extra = Extra::create(['user_id' => $request->user()->id] + $this->validated($request));

        return response()->json($this->present($extra), 201);
    }

    public function update(Request $request, Extra $extra): JsonResponse
    {
        abort_unless($extra->user_id === $request->user()->id, 403);
        $extra->update($this->validated($request));

        return response()->json($this->present($extra));
    }

    public function destroy(Request $request, Extra $extra): JsonResponse
    {
        abort_unless($extra->user_id === $request->user()->id, 403);
        $extra->delete();

        return response()->json(null, 204);
    }

    /** @return array{label: string, aisle_id: string|null} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'aisleId' => ['nullable', 'exists:aisles,id'],
        ]);

        return ['label' => trim($data['label']), 'aisle_id' => $data['aisleId'] ?? null];
    }

    /** @return array<string, mixed> */
    private function present(Extra $extra): array
    {
        return ['id' => $extra->id, 'label' => $extra->label, 'aisleId' => $extra->aisle_id];
    }
}
