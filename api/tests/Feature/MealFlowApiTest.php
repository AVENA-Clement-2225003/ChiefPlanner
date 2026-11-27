<?php

namespace Tests\Feature;

use App\Models\MealFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesDishes;
use Tests\TestCase;

class MealFlowApiTest extends TestCase
{
    use MakesDishes;
    use RefreshDatabase;

    public function test_a_user_creates_and_lists_their_meal_flows(): void
    {
        $this->actingAs($user = User::factory()->create());
        MealFlow::create(['owner_id' => User::factory()->create()->id, 'name' => 'Pas à moi']);

        $this->postJson('/api/meal-flows', ['name' => 'Repas du dimanche'])->assertCreated()->assertJsonPath('dishIds', []);

        $this->getJson('/api/meal-flows')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Repas du dimanche');
    }

    public function test_dishes_are_set_from_the_users_catalogue(): void
    {
        $this->actingAs($user = User::factory()->create());
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Asiatique']);
        $mine = $this->makeDish($user);

        $this->putJson("/api/meal-flows/{$flow->id}", ['dishIds' => [$mine->id]])->assertOk()->assertJsonPath('dishIds', [$mine->id]);
    }

    public function test_a_dish_outside_the_catalogue_is_refused(): void
    {
        $this->actingAs($user = User::factory()->create());
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Asiatique']);
        $foreign = $this->makeDish(User::factory()->create(), shared: true);

        $this->putJson("/api/meal-flows/{$flow->id}", ['dishIds' => [$foreign->id]])->assertUnprocessable();
    }

    public function test_an_adopted_dish_can_join_a_meal_flow(): void
    {
        $foreign = $this->makeDish(User::factory()->create(), shared: true);
        $this->actingAs($user = User::factory()->create());
        $this->putJson("/api/dishes/{$foreign->id}/subscription")->assertOk();
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Rapide']);

        $this->putJson("/api/meal-flows/{$flow->id}", ['dishIds' => [$foreign->id]])->assertOk();
    }

    public function test_only_the_owner_edits_or_deletes_a_meal_flow(): void
    {
        $flow = MealFlow::create(['owner_id' => User::factory()->create()->id, 'name' => 'Privé']);
        $this->actingAs(User::factory()->create());

        $this->putJson("/api/meal-flows/{$flow->id}", ['name' => 'Volé'])->assertForbidden();
        $this->deleteJson("/api/meal-flows/{$flow->id}")->assertForbidden();
    }

    public function test_a_dish_that_stops_being_shared_leaves_the_meal_flows_of_those_who_adopted_it(): void
    {
        $owner = User::factory()->create();
        $dish = $this->makeDish($owner, shared: true);
        $this->actingAs($adopter = User::factory()->create());
        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk();
        $flow = MealFlow::create(['owner_id' => $adopter->id, 'name' => 'Rapide']);
        $this->putJson("/api/meal-flows/{$flow->id}", ['dishIds' => [$dish->id]])->assertOk();

        $this->actingAs($owner);
        $this->putJson("/api/dishes/{$dish->id}/share", ['isShared' => false])->assertOk();

        $this->assertDatabaseCount('meal_flow_dish', 0);
    }

    public function test_a_meal_flow_can_be_deleted_without_deleting_its_dishes(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Temp']);
        $flow->dishes()->attach($dish);

        $this->deleteJson("/api/meal-flows/{$flow->id}")->assertNoContent();

        $this->assertDatabaseHas('dishes', ['id' => $dish->id]);
    }
}
