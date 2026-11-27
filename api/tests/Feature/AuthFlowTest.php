<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakesAuthentik;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use FakesAuthentik;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthentik();
    }

    /** Starts the flow and returns the `state` and `nonce` the app generated. */
    private function startLogin(): array
    {
        $this->fakeAuthentikLogin('placeholder', []);
        $response = $this->get('/api/auth/login');
        $flow = session('oidc');

        $response->assertRedirectContains('https://auth.test/application/o/authorize/');
        $response->assertRedirectContains('code_challenge_method=S256');

        return [$flow['state'], $flow['nonce']];
    }

    public function test_user_in_the_user_group_logs_in_as_non_admin(): void
    {
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-user']);

        $this->get("/api/auth/callback?code=abc&state=$state")->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['sub' => 'sub-1', 'email' => 'alice@example.org', 'is_admin' => false]);
        $this->getJson('/api/me')->assertOk()->assertJson(['name' => 'Alice', 'isAdmin' => false]);
    }

    public function test_user_in_the_admin_group_becomes_admin_and_loses_it_when_removed_from_the_group(): void
    {
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-admin']);
        $this->get("/api/auth/callback?code=abc&state=$state");
        $this->assertTrue(User::firstWhere('sub', 'sub-1')->is_admin);

        $this->post('/api/auth/logout');
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-user']);
        $this->get("/api/auth/callback?code=abc&state=$state");

        $this->assertFalse(User::firstWhere('sub', 'sub-1')->is_admin);
    }

    public function test_user_outside_both_groups_is_refused(): void
    {
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['some-other-group']);

        $this->get("/api/auth/callback?code=abc&state=$state")->assertRedirect('/?authError=forbidden');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_wrong_state_is_refused(): void
    {
        [, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-user']);

        $this->get('/api/auth/callback?code=abc&state=forged')->assertRedirect('/?authError=denied');

        $this->assertGuest();
    }

    public function test_wrong_nonce_is_refused(): void
    {
        [$state] = $this->startLogin();
        $this->fakeAuthentikLogin('another-nonce', ['chiefplanner-dev-user']);

        $this->get("/api/auth/callback?code=abc&state=$state")->assertRedirect('/?authError=failed');

        $this->assertGuest();
    }

    public function test_token_for_another_client_is_refused(): void
    {
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-user'], ['aud' => 'someone-else']);

        $this->get("/api/auth/callback?code=abc&state=$state")->assertRedirect('/?authError=failed');

        $this->assertGuest();
    }

    public function test_expired_token_is_refused(): void
    {
        [$state, $nonce] = $this->startLogin();
        $this->fakeAuthentikLogin($nonce, ['chiefplanner-dev-user'], ['exp' => time() - 3600, 'iat' => time() - 4000]);

        $this->get("/api/auth/callback?code=abc&state=$state")->assertRedirect('/?authError=failed');

        $this->assertGuest();
    }

    public function test_me_is_unauthorized_without_a_session(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_ends_the_session_and_returns_the_authentik_logout_url(): void
    {
        $this->fakeAuthentikLogin('n', []);
        $this->actingAs(User::factory()->create());

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['endSessionUrl' => 'https://auth.test/application/o/chiefplanner-dev/end-session/']);

        $this->assertGuest();
    }
}
