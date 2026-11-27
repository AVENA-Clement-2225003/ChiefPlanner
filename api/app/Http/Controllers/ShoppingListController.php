<?php

namespace App\Http\Controllers;

use App\Shopping\ShoppingListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    public function __construct(private readonly ShoppingListService $shoppingList) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json($this->shoppingList->build($request->user()));
    }

    /** Ticks or unticks an item of the list while shopping. */
    public function tick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'checked' => ['required', 'boolean'],
        ]);
        $row = ['user_id' => $request->user()->id, 'item_key' => $data['key']];

        $data['checked'] ? DB::table('shopping_ticks')->insertOrIgnore($row) : DB::table('shopping_ticks')->where($row)->delete();

        return response()->json(['key' => $data['key'], 'checked' => $data['checked']]);
    }
}
