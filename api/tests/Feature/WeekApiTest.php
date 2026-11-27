<?php

namespace Tests\Feature;

use App\Models\MealFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesDishes;
use Tests\TestCase;

class WeekApiTest extends TestCase
{
    use MakesDishes;
    use RefreshDatabase;

    /** @return array<string, mixed> every Slot enabled for 2 people, with no MealFlow */
    private function fullPreferences(array $overrides = []): array
    {
        $slots = [];
        foreach (range(0, 6) as $day) {
            foreach (['lunch', 'dinner'] as $slot) {
                $slots[] = ['day' => $day, 'slot' => $slot, 'enabled' => true, 'servings' => 2, 'flow' => null];
            }
        }

        return array_merge(['mealFlowAId' => null, 'mealFlowBId' => null, 'slots' => $slots], $overrides);
    }

    public function test_preferences_default_to_every_slot_enabled_for_one_person(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/preferences')->assertOk();

        $response->assertJsonCount(14, 'slots')->assertJsonPath('slots.0', ['day' => 0, 'slot' => 'lunch', 'enabled' => true, 'servings' => 1, 'flow' => null]);
    }

    public function test_preferences_are_saved_and_read_back(): void
    {
        $this->actingAs($user = User::factory()->create());
        $payload = $this->fullPreferences();
        $payload['slots'][0]['enabled'] = false;
        $payload['slots'][1]['servings'] = 4;

        $this->putJson('/api/preferences', $payload)->assertOk();

        $this->getJson('/api/preferences')->assertJsonPath('slots.0.enabled', false)->assertJsonPath('slots.1.servings', 4);
    }

    public function test_a_meal_flow_of_someone_else_cannot_be_applied(): void
    {
        $this->actingAs(User::factory()->create());
        $foreign = MealFlow::create(['owner_id' => User::factory()->create()->id, 'name' => 'Pas à moi']);

        $this->putJson('/api/preferences', $this->fullPreferences(['mealFlowAId' => $foreign->id]))->assertUnprocessable();
    }

    public function test_a_proposal_fills_the_enabled_slots_with_dishes_that_suit_the_slot(): void
    {
        $this->actingAs($user = User::factory()->create());
        $lunchOnly = $this->makeDish($user, 'Salade', ['lunch']);
        $dinnerOnly = $this->makeDish($user, 'Soupe', ['dinner']);
        $payload = $this->fullPreferences();
        foreach ($payload['slots'] as $index => $slot) {
            $payload['slots'][$index]['enabled'] = $slot['day'] < 2;
        }
        $this->putJson('/api/preferences', $payload)->assertOk();

        $response = $this->postJson('/api/week/proposal')->assertOk();

        $response->assertJsonCount(4, 'meals');
        foreach ($response->json('meals') as $meal) {
            $expected = $meal['slot'] === 'lunch' ? $lunchOnly->id : $dinnerOnly->id;
            $this->assertSame($expected, $meal['dishId']);
            $this->assertSame(2, $meal['servings']);
        }
    }

    public function test_a_slot_with_no_suitable_dish_is_kept_empty_to_replace(): void
    {
        $this->actingAs($user = User::factory()->create());
        $this->makeDish($user, 'Salade', ['lunch']);
        $this->putJson('/api/preferences', $this->fullPreferences())->assertOk();

        $meals = collect($this->postJson('/api/week/proposal')->json('meals'));

        $dinners = $meals->where('slot', 'dinner');
        $this->assertCount(7, $dinners);
        $this->assertTrue($dinners->every(fn ($meal) => $meal['dishId'] === null && $meal['needsReplacement']));
    }

    public function test_every_dish_is_used_before_one_is_repeated(): void
    {
        $this->actingAs($user = User::factory()->create());
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $name) {
            $this->makeDish($user, $name, ['lunch']);
        }
        $this->putJson('/api/preferences', $this->fullPreferences())->assertOk();

        $lunches = collect($this->postJson('/api/week/proposal')->json('meals'))->where('slot', 'lunch');

        $this->assertCount(7, $lunches->pluck('dishId')->unique());
    }

    public function test_a_meal_flow_restricts_the_slots_that_choose_it(): void
    {
        $this->actingAs($user = User::factory()->create());
        $weekend = $this->makeDish($user, 'Burger');
        $this->makeDish($user, 'Salade');
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Week-end']);
        $flow->dishes()->attach($weekend);
        $payload = $this->fullPreferences(['mealFlowAId' => $flow->id]);
        foreach ($payload['slots'] as $index => $slot) {
            $payload['slots'][$index]['flow'] = $slot['day'] >= 5 ? 'a' : null;
        }
        $this->putJson('/api/preferences', $payload)->assertOk();

        $meals = collect($this->postJson('/api/week/proposal')->json('meals'));

        $this->assertTrue($meals->where('day', '>=', 5)->every(fn ($meal) => $meal['dishId'] === $weekend->id));
    }

    public function test_a_meal_flow_with_nothing_for_the_slot_falls_back_to_the_whole_catalogue(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Salade');
        $flow = MealFlow::create(['owner_id' => $user->id, 'name' => 'Vide']);
        $payload = $this->fullPreferences(['mealFlowAId' => $flow->id]);
        foreach ($payload['slots'] as $index => $slot) {
            $payload['slots'][$index]['flow'] = 'a';
        }
        $this->putJson('/api/preferences', $payload)->assertOk();

        $meals = collect($this->postJson('/api/week/proposal')->json('meals'));

        $this->assertTrue($meals->every(fn ($meal) => $meal['dishId'] === $dish->id));
    }

    public function test_a_new_proposal_can_be_undone_once(): void
    {
        $this->actingAs($user = User::factory()->create());
        $first = $this->makeDish($user, 'Premier');
        $this->putJson('/api/preferences', $this->fullPreferences())->assertOk();
        $this->postJson('/api/week/proposal')->assertOk()->assertJsonPath('hasPrevious', false);
        $second = $this->makeDish($user, 'Second');
        $first->delete();

        $this->postJson('/api/week/proposal')->assertOk()->assertJsonPath('hasPrevious', true);
        $reverted = $this->postJson('/api/week/revert')->assertOk()->assertJsonPath('hasPrevious', false);

        // The previous Week used a dish that no longer exists: the meals are kept, to replace.
        $this->assertTrue(collect($reverted->json('meals'))->every(fn ($meal) => $meal['needsReplacement']));
        $this->assertNotSame($second->id, $reverted->json('meals.0.dishId'));
        $this->postJson('/api/week/revert')->assertStatus(409);
    }

    public function test_changing_a_meal_is_saved(): void
    {
        $this->actingAs($user = User::factory()->create());
        $chosen = $this->makeDish($user, 'Curry');

        $this->putJson('/api/week/meals/2/dinner', ['dishId' => $chosen->id, 'servings' => 4])
            ->assertOk()
            ->assertJsonPath('dishName', 'Curry')
            ->assertJsonPath('servings', 4);

        $this->getJson('/api/week')->assertJsonCount(1, 'meals')->assertJsonPath('meals.0.day', 2);
    }

    public function test_changing_only_the_servings_keeps_the_dish(): void
    {
        $this->actingAs($user = User::factory()->create());
        $chosen = $this->makeDish($user, 'Curry');
        $this->putJson('/api/week/meals/2/dinner', ['dishId' => $chosen->id])->assertOk();

        $this->putJson('/api/week/meals/2/dinner', ['servings' => 6])->assertOk()->assertJsonPath('dishName', 'Curry');
    }

    public function test_a_dish_outside_the_catalogue_cannot_be_planned(): void
    {
        $this->actingAs(User::factory()->create());
        $foreign = $this->makeDish(User::factory()->create(), shared: true);

        $this->putJson('/api/week/meals/0/lunch', ['dishId' => $foreign->id])->assertUnprocessable();
    }

    public function test_the_browser_can_restore_an_older_week(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Curry');

        $this->putJson('/api/week', ['meals' => [['day' => 1, 'slot' => 'lunch', 'dishId' => $dish->id, 'servings' => 3]]])
            ->assertOk()
            ->assertJsonPath('meals.0.dishName', 'Curry');
    }

    public function test_a_dish_that_disappears_leaves_its_meal_to_replace(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Curry');
        $this->putJson('/api/week/meals/0/lunch', ['dishId' => $dish->id, 'servings' => 2])->assertOk();

        $dish->delete();

        $this->getJson('/api/week')->assertJsonPath('meals.0.needsReplacement', true)->assertJsonPath('meals.0.servings', 2);
    }

    public function test_each_user_sees_only_their_own_week(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $this->putJson('/api/week/meals/0/lunch', ['servings' => 2])->assertOk();

        $this->actingAs(User::factory()->create());

        $this->getJson('/api/week')->assertJsonCount(0, 'meals');
    }
}
