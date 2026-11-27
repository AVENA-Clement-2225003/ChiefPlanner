<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        // Explicit 200: a resource answers 201 when its model was created during this same request cycle.
        return (new UserResource($request->user()))->response()->setStatusCode(200);
    }
}
