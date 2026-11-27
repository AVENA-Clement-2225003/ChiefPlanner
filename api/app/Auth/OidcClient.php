<?php

namespace App\Auth;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Authorization Code + PKCE flow against Authentik, run on the server (ADR 0001). */
class OidcClient
{
    private const METADATA_TTL_SECONDS = 3600;

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        $issuer = rtrim((string) config('oidc.issuer'), '/');

        return Cache::remember('oidc.metadata.'.md5($issuer), self::METADATA_TTL_SECONDS, function () use ($issuer) {
            return Http::get($issuer.'/.well-known/openid-configuration')->throw()->json();
        });
    }

    public function authorizationUrl(string $state, string $nonce, string $codeVerifier): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('oidc.client_id'),
            'redirect_uri' => config('oidc.redirect_uri'),
            'scope' => 'openid profile email groups',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $this->codeChallenge($codeVerifier),
            'code_challenge_method' => 'S256',
        ]);

        return $this->metadata()['authorization_endpoint'].'?'.$query;
    }

    public function endSessionUrl(): ?string
    {
        return $this->metadata()['end_session_endpoint'] ?? null;
    }

    public function identityFromCode(string $code, string $codeVerifier, string $expectedNonce): OidcIdentity
    {
        $response = Http::asForm()->post($this->metadata()['token_endpoint'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('oidc.redirect_uri'),
            'client_id' => config('oidc.client_id'),
            'client_secret' => config('oidc.client_secret'),
            'code_verifier' => $codeVerifier,
        ]);

        $idToken = $response->json('id_token');
        if (! $response->successful() || ! is_string($idToken)) {
            throw new OidcException('Token endpoint refused the authorization code.');
        }

        return $this->verifyIdToken($idToken, $expectedNonce);
    }

    public function verifyIdToken(string $idToken, string $expectedNonce): OidcIdentity
    {
        try {
            $claims = JWT::decode($idToken, JWK::parseKeySet($this->jwks(), 'RS256'));
        } catch (Throwable $error) {
            throw new OidcException('ID token signature or expiry is invalid: '.$error->getMessage(), 0, $error);
        }

        $this->assertClaims($claims, $expectedNonce);

        return new OidcIdentity(
            sub: (string) $claims->sub,
            email: (string) ($claims->email ?? ''),
            name: (string) ($claims->name ?? $claims->preferred_username ?? $claims->email ?? ''),
            groups: array_values(array_map('strval', (array) ($claims->groups ?? []))),
        );
    }

    private function assertClaims(object $claims, string $expectedNonce): void
    {
        $audience = (array) ($claims->aud ?? []);
        $issuer = rtrim((string) config('oidc.issuer'), '/');

        $checks = [
            'issuer' => rtrim((string) ($claims->iss ?? ''), '/') === $issuer,
            'audience' => in_array(config('oidc.client_id'), $audience, true),
            'nonce' => hash_equals($expectedNonce, (string) ($claims->nonce ?? '')),
            'subject' => ! empty($claims->sub),
        ];

        $failed = array_keys(array_filter($checks, fn (bool $passed) => ! $passed));
        if ($failed !== []) {
            throw new OidcException('ID token claim check failed: '.implode(', ', $failed));
        }
    }

    /** @return array<string, mixed> */
    private function jwks(): array
    {
        $uri = $this->metadata()['jwks_uri'];

        return Cache::remember('oidc.jwks.'.md5($uri), self::METADATA_TTL_SECONDS, fn () => Http::get($uri)->throw()->json());
    }

    private function codeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }
}
