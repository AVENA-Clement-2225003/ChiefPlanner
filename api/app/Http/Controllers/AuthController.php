<?php

namespace App\Http\Controllers;

use App\Auth\OidcClient;
use App\Auth\OidcException;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const SESSION_KEY = 'oidc';

    public function __construct(private readonly OidcClient $oidc) {}

    public function login(Request $request): RedirectResponse
    {
        $flow = [
            'state' => Str::random(40),
            'nonce' => Str::random(40),
            'verifier' => Str::random(64),
        ];
        $request->session()->put(self::SESSION_KEY, $flow);

        return redirect()->away($this->oidc->authorizationUrl($flow['state'], $flow['nonce'], $flow['verifier']));
    }

    public function callback(Request $request): RedirectResponse
    {
        $flow = $request->session()->pull(self::SESSION_KEY);
        $stateIsValid = is_array($flow) && hash_equals($flow['state'], (string) $request->query('state'));
        if (! $stateIsValid || $request->query('error') || ! $request->query('code')) {
            return $this->backToApp('denied');
        }

        try {
            $identity = $this->oidc->identityFromCode((string) $request->query('code'), $flow['verifier'], $flow['nonce']);
        } catch (OidcException $error) {
            Log::warning('OIDC login failed: '.$error->getMessage());

            return $this->backToApp('failed');
        }

        $isAdmin = $identity->isInGroup(config('oidc.group_admin'));
        if (! $isAdmin && ! $identity->isInGroup(config('oidc.group_user'))) {
            return $this->backToApp('forbidden');
        }

        $user = User::updateOrCreate(
            ['sub' => $identity->sub],
            ['email' => $identity->email, 'name' => $identity->name, 'is_admin' => $isAdmin],
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['endSessionUrl' => $this->oidc->endSessionUrl()]);
    }

    private function backToApp(string $error): RedirectResponse
    {
        return redirect('/?authError='.$error);
    }
}
