<?php

namespace App\Local;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sign-in for working on the interface on your own machine, without Authentik.
 * The route exists only when APP_ENV=local (see routes/web.php): it must never be reachable in production.
 */
class DevLoginController extends Controller
{
    public function __invoke(Request $request, DemoData $demo): RedirectResponse
    {
        $user = User::firstOrCreate(
            ['sub' => 'local-dev'],
            ['email' => 'local@chiefplanner.test', 'name' => 'Chef Local', 'is_admin' => true],
        );
        $demo->seedFor($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->away(env('LOCAL_SPA_URL', 'http://localhost:5173'));
    }
}
