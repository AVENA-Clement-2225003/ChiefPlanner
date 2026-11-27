<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Meal;
use App\Models\MealFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MakesDishes;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use MakesDishes;
    use RefreshDatabase;

    public function test_everything_a_user_owns_is_erased(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Curry');
        $this->addLine($dish, 'Riz', 100, 'g');
        MealFlow::create(['owner_id' => $user->id, 'name' => 'Flow'])->dishes()->attach($dish);
        Meal::create(['user_id' => $user->id, 'day' => 0, 'slot' => 'lunch', 'dish_id' => $dish->id, 'servings' => 2]);
        $this->postJson('/api/extras', ['label' => 'PQ'])->assertCreated();
        $this->postJson('/api/spendings', ['amount' => 12])->assertCreated();
        $this->putJson('/api/preferences', ['slots' => collect(range(0, 6))->flatMap(fn ($day) => [
            ['day' => $day, 'slot' => 'lunch', 'enabled' => true, 'servings' => 1, 'flow' => null],
            ['day' => $day, 'slot' => 'dinner', 'enabled' => true, 'servings' => 1, 'flow' => null],
        ])->all()])->assertOk();

        $this->deleteJson('/api/me')->assertNoContent();

        foreach (['users', 'dishes', 'dish_ingredients', 'meal_flows', 'meal_flow_dish', 'meals', 'extras', 'spendings', 'slot_preferences', 'preferences'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertGuest();
    }

    public function test_a_shared_dish_disappears_for_those_who_adopted_it_and_their_meal_is_left_to_replace(): void
    {
        $owner = User::factory()->create();
        $dish = $this->makeDish($owner, 'Curry', shared: true);
        $this->actingAs($adopter = User::factory()->create());
        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk();
        $this->putJson('/api/week/meals/0/lunch', ['dishId' => $dish->id, 'servings' => 3])->assertOk();

        $this->actingAs($owner);
        $this->deleteJson('/api/me')->assertNoContent();

        $this->actingAs($adopter);
        $this->getJson('/api/dishes')->assertJsonCount(0, 'data');
        $this->getJson('/api/week')->assertJsonPath('meals.0.needsReplacement', true)->assertJsonPath('meals.0.servings', 3);
    }

    public function test_ingredients_stay_when_a_user_leaves(): void
    {
        $leaver = User::factory()->create();
        $stayer = User::factory()->create();
        $dish = $this->makeDish($leaver, 'Pâtes');
        $tomato = $this->addLine($dish, 'Tomate', 100, 'g');
        $this->actingAs($stayer)->postJson('/api/ingredients', ['name' => 'Sel'])->assertCreated();

        $this->actingAs($leaver)->deleteJson('/api/me')->assertNoContent();

        $this->assertDatabaseHas('ingredients', ['id' => $tomato->id]);
        $this->actingAs($stayer)->getJson('/api/ingredients?q=tomate')->assertJsonCount(1, 'data');
    }

    public function test_an_admin_can_erase_another_user_but_not_themselves_that_way(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();
        $this->actingAs($admin);

        $this->deleteJson("/api/admin/users/{$admin->id}")->assertUnprocessable();
        $this->deleteJson("/api/admin/users/{$other->id}")->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_a_regular_user_cannot_erase_somebody_else(): void
    {
        $other = User::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->deleteJson("/api/admin/users/{$other->id}")->assertForbidden();
    }

    public function test_an_admin_merges_a_duplicate_ingredient_into_another(): void
    {
        $owner = User::factory()->create();
        $first = $this->makeDish($owner, 'Salade');
        $second = $this->makeDish($owner, 'Soupe');
        $both = $this->makeDish($owner, 'Mixte');
        $tomate = $this->addLine($first, 'Tomate', 100, 'g');
        $tomates = Ingredient::create(['name' => 'Tomates', 'name_key' => 'tomates']);
        $second->lines()->create(['ingredient_id' => $tomates->id, 'quantity_per_person' => 2, 'unit_id' => 'unit']);
        $both->lines()->create(['ingredient_id' => $tomate->id, 'quantity_per_person' => 50, 'unit_id' => 'g']);
        $both->lines()->create(['ingredient_id' => $tomates->id, 'quantity_per_person' => 1, 'unit_id' => 'unit']);
        $this->actingAs(User::factory()->admin()->create());

        $this->postJson("/api/admin/ingredients/{$tomates->id}/merge", ['intoId' => $tomate->id])->assertNoContent();

        $this->assertDatabaseMissing('ingredients', ['id' => $tomates->id]);
        $this->assertSame(3, DB::table('dish_ingredients')->where('ingredient_id', $tomate->id)->count());
    }

    public function test_only_admins_moderate_ingredients(): void
    {
        $ingredient = Ingredient::create(['name' => 'Sel', 'name_key' => 'sel']);
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/admin/ingredients')->assertForbidden();
        $this->deleteJson("/api/admin/ingredients/{$ingredient->id}")->assertForbidden();
    }

    public function test_an_admin_cannot_delete_an_ingredient_that_is_in_use(): void
    {
        $dish = $this->makeDish($owner = User::factory()->create());
        $used = $this->addLine($dish, 'Sel', 1, 'g');
        $this->actingAs(User::factory()->admin()->create());

        $this->deleteJson("/api/admin/ingredients/{$used->id}")->assertStatus(409);
        $this->getJson('/api/admin/ingredients')->assertJsonPath('data.0.usedByDishes', 1);
    }
}
