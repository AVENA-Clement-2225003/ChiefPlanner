<?php

namespace Tests\Support;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/** Replaces Authentik with HTTP fakes: discovery, JWKS, token endpoint and management API. */
trait FakesAuthentik
{
    private const ISSUER = 'https://auth.test/application/o/chiefplanner-dev/';

    private const CLIENT_ID = 'client-123';

    private \OpenSSLAsymmetricKey $signingKey;

    protected function setUpAuthentik(): void
    {
        config([
            'oidc.issuer' => self::ISSUER,
            'oidc.client_id' => self::CLIENT_ID,
            'oidc.client_secret' => 'secret',
            'oidc.redirect_uri' => 'http://localhost/api/auth/callback',
            'oidc.group_user' => 'chiefplanner-dev-user',
            'oidc.group_admin' => 'chiefplanner-dev-admin',
            'oidc.authentik.api_url' => 'https://auth.test/api/v3',
            'oidc.authentik.api_token' => 'service-token',
            'oidc.authentik.admin_group_id' => 'group-uuid',
        ]);
        Cache::flush();

        $this->signingKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    }

    /**
     * @param  list<string>  $groups
     * @param  array<string, mixed>  $overrides  claims that replace the defaults (e.g. `nonce`, `aud`)
     */
    protected function fakeAuthentikLogin(string $nonce, array $groups, array $overrides = []): void
    {
        $claims = array_merge([
            'iss' => self::ISSUER,
            'aud' => self::CLIENT_ID,
            'sub' => 'sub-1',
            'email' => 'alice@example.org',
            'name' => 'Alice',
            'groups' => $groups,
            'nonce' => $nonce,
            'iat' => time(),
            'exp' => time() + 300,
        ], $overrides);

        // A fresh factory each time: stubs of an earlier call would otherwise win over these.
        Http::swap(new Factory);
        Http::fake([
            self::ISSUER.'.well-known/openid-configuration' => Http::response([
                'authorization_endpoint' => 'https://auth.test/application/o/authorize/',
                'token_endpoint' => 'https://auth.test/application/o/token/',
                'jwks_uri' => 'https://auth.test/application/o/chiefplanner-dev/jwks/',
                'end_session_endpoint' => 'https://auth.test/application/o/chiefplanner-dev/end-session/',
            ]),
            'https://auth.test/application/o/chiefplanner-dev/jwks/' => Http::response($this->jwks()),
            'https://auth.test/application/o/token/' => Http::response([
                'id_token' => JWT::encode($claims, $this->signingKey, 'RS256', 'kid-1'),
            ]),
        ]);
    }

    /** @return array{keys: list<array<string, string>>} */
    private function jwks(): array
    {
        $details = openssl_pkey_get_details($this->signingKey)['rsa'];
        $encode = fn (string $bytes) => rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');

        return ['keys' => [[
            'kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256', 'kid' => 'kid-1',
            'n' => $encode($details['n']), 'e' => $encode($details['e']),
        ]]];
    }
}
