<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;

/** The fixed lists the forms are built from. */
class CatalogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'units' => Unit::query()->orderBy('position')->get(['id', 'label', 'family', 'factor']),
            'tags' => Tag::query()->orderBy('position')->get(['id', 'label', 'kind']),
        ]);
    }
}
