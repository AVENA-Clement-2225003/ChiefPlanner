<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/** A controller, not a closure: closures cannot be serialised by `route:cache`, which the image runs at startup. */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
