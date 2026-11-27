<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakesAuthentik;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use FakesAuthentik;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthentik();
    }

    public function test_a_regular_user_cannot_list_users(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_an_admin_lists_users(): void
    {
        User::factory()->count(2)->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->getJson('/api/admin/users')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_granting_admin_adds_the_user_to_the_authentik_group(): void
    {
        Http::fake([
            'https://auth.test/api/v3/core/users/*' => Http::response(['results' => [['pk' => 42, 'email' => 'bob@example.org']]]),
            'https://auth.test/api/v3/core/groups/group-uuid/add_user/' => Http::response('', 204),
        ]);
        $bob = User::factory()->create(['email' => 'bob@example.org']);
        $this->actingAs(User::factory()->admin()->create());

        $this->putJson("/api/admin/users/{$bob->id}/admin")->assertOk()->assertJson(['isAdmin' => true]);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/add_user/')
            && $request['pk'] === 42
            && $request->hasHeader('Authorization', 'Bearer service-token'));
        $this->assertTrue($bob->fresh()->is_admin);
    }

    public function test_revoking_admin_removes_the_user_from_the_authentik_group(): void
    {
        Http::fake([
            'https://auth.test/api/v3/core/users/*' => Http::response(['results' => [['pk' => 7, 'email' => 'bob@example.org']]]),
            'https://auth.test/api/v3/core/groups/group-uuid/remove_user/' => Http::response('', 204),
        ]);
        $bob = User::factory()->admin()->create(['email' => 'bob@example.org']);
        $this->actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/admin/users/{$bob->id}/admin")->assertOk()->assertJson(['isAdmin' => false]);

        $this->assertFalse($bob->fresh()->is_admin);
    }

    public function test_an_admin_cannot_revoke_their_own_role(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->deleteJson("/api/admin/users/{$admin->id}/admin")->assertUnprocessable();

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_the_role_is_left_untouched_when_authentik_fails(): void
    {
        Http::fake(['https://auth.test/*' => Http::response('boom', 500)]);
        $bob = User::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->putJson("/api/admin/users/{$bob->id}/admin")->assertStatus(502);

        $this->assertFalse($bob->fresh()->is_admin);
    }

    public function test_the_lookup_matches_the_email_exactly(): void
    {
        Http::fake([
            'https://auth.test/api/v3/core/users/*' => Http::response(['results' => [['pk' => 1, 'email' => 'bobby@example.org']]]),
        ]);
        $bob = User::factory()->create(['email' => 'bob@example.org']);
        $this->actingAs(User::factory()->admin()->create());

        $this->putJson("/api/admin/users/{$bob->id}/admin")->assertStatus(502);

        $this->assertFalse($bob->fresh()->is_admin);
    }
}
