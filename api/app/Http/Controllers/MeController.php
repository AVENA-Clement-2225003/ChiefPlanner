<?php

namespace App\Http\Controllers;

use App\Account\AccountDeletion;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        // Explicit 200: a resource answers 201 when its model was created during this same request cycle.
        return (new UserResource($request->user()))->response()->setStatusCode(200);
    }

    /** "Delete my account": erases the user's data and ends the session (RGPD). */
    public function destroy(Request $request, AccountDeletion $deletion): JsonResponse
    {
        $deletion->erase($request->user());

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }
}
