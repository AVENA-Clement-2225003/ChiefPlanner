<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function dishPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Gnocchis nuggets',
            'servings' => 2,
            'slots' => ['lunch', 'dinner'],
            'tagIds' => ['quick'],
            'ingredients' => [
                ['ingredientName' => 'Gnocchis', 'quantity' => 2, 'unitId' => 'portion'],
                ['ingredientName' => 'Nuggets', 'quantity' => 10, 'unitId' => 'unit'],
            ],
            'steps' => ['Cuire les gnocchis', 'Poêler les nuggets'],
        ], $overrides);
    }

    public function test_the_catalog_exposes_the_fixed_lists(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->getJson('/api/catalog')->assertOk();

        $response->assertJsonFragment(['id' => 'kg', 'family' => 'mass']);
        $response->assertJsonFragment(['id' => 'vegetarian', 'kind' => 'diet']);
    }

    public function test_quantities_typed_for_n_people_are_stored_for_one_person(): void
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->postJson('/api/dishes', $this->dishPayload())->assertCreated();

        $response->assertJsonPath('dish.ingredients.0.quantityPerPerson', 1)
            ->assertJsonPath('dish.ingredients.1.quantityPerPerson', 5)
            ->assertJsonPath('warnings', [])
            ->assertJsonPath('dish.steps.1.position', 2)
            ->assertJsonPath('dish.ownedByCurrentUser', true);
        $this->assertDatabaseHas('dish_ingredients', ['quantity_per_person' => 5.0, 'unit_id' => 'unit']);
        $this->assertSame($user->id, Dish::first()->owner_id);
    }

    public function test_a_fractional_count_per_person_is_saved_with_a_warning(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = $this->dishPayload(['ingredients' => [['ingredientName' => 'Oeuf', 'quantity' => 3, 'unitId' => 'unit']]]);

        $response = $this->postJson('/api/dishes', $payload)->assertCreated();

        $response->assertJsonPath('warnings.0.ingredient', 'Oeuf')->assertJsonPath('warnings.0.quantityPerPerson', 1.5);
    }

    public function test_a_fraction_of_a_mass_is_not_a_warning(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = $this->dishPayload(['servings' => 3, 'ingredients' => [['ingredientName' => 'Riz', 'quantity' => 100, 'unitId' => 'g']]]);

        $this->postJson('/api/dishes', $payload)->assertCreated()->assertJsonPath('warnings', []);
    }

    public function test_an_existing_ingredient_is_reused_by_name_across_the_community(): void
    {
        $other = User::factory()->create();
        $shared = Ingredient::create(['owner_id' => $other->id, 'name' => 'Crème fraîche', 'name_key' => 'creme fraiche', 'is_shared' => true]);
        $this->actingAs(User::factory()->create());

        $this->postJson('/api/ingredients', ['name' => '  CREME   fraiche '])
            ->assertOk()
            ->assertJsonPath('id', $shared->id);

        $this->assertDatabaseCount('ingredients', 1);
    }

    public function test_a_private_ingredient_of_someone_else_is_not_reused(): void
    {
        $other = User::factory()->create();
        Ingredient::create(['owner_id' => $other->id, 'name' => 'Tomate', 'name_key' => 'tomate', 'is_shared' => false]);
        $this->actingAs(User::factory()->create());

        $this->postJson('/api/ingredients', ['name' => 'Tomate'])->assertCreated();

        $this->assertDatabaseCount('ingredients', 2);
    }

    public function test_the_same_ingredient_twice_in_a_dish_is_refused(): void
    {
        $this->actingAs(User::factory()->create());
        $payload = $this->dishPayload(['ingredients' => [
            ['ingredientName' => 'Riz', 'quantity' => 100, 'unitId' => 'g'],
            ['ingredientName' => 'riz', 'quantity' => 1, 'unitId' => 'kg'],
        ]]);

        $this->postJson('/api/dishes', $payload)->assertUnprocessable();
    }

    public function test_a_private_dish_is_invisible_to_others(): void
    {
        $dish = $this->dishOf(User::factory()->create());
        $this->actingAs(User::factory()->create());

        $this->getJson("/api/dishes/{$dish->id}")->assertForbidden();
        $this->getJson('/api/dishes/shared')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_only_the_owner_can_edit_or_delete_a_dish(): void
    {
        $dish = $this->dishOf(User::factory()->create(), shared: true);
        $this->actingAs(User::factory()->create());

        $this->putJson("/api/dishes/{$dish->id}", $this->dishPayload())->assertForbidden();
        $this->deleteJson("/api/dishes/{$dish->id}")->assertForbidden();
    }

    public function test_a_shared_dish_can_be_found_and_adopted(): void
    {
        $dish = $this->dishOf(User::factory()->create(), shared: true);
        $this->actingAs($adopter = User::factory()->create());

        $this->getJson('/api/dishes/shared')->assertOk()->assertJsonCount(1, 'data');
        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk()->assertJsonPath('subscribed', true);

        $this->getJson('/api/dishes')->assertOk()->assertJsonCount(1, 'data');
        $this->assertTrue($dish->fresh()->subscribers->contains($adopter));
    }

    public function test_the_owner_cannot_adopt_their_own_dish(): void
    {
        $this->actingAs($owner = User::factory()->create());
        $dish = $this->dishOf($owner, shared: true);

        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertForbidden();
    }

    public function test_unsharing_a_dish_removes_it_from_those_who_adopted_it(): void
    {
        $owner = User::factory()->create();
        $dish = $this->dishOf($owner, shared: true);
        $this->actingAs($adopter = User::factory()->create());
        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk();

        $this->actingAs($owner);
        $this->putJson("/api/dishes/{$dish->id}/share", ['isShared' => false])->assertOk();

        $this->actingAs($adopter);
        $this->getJson('/api/dishes')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/dishes/{$dish->id}")->assertForbidden();
    }

    public function test_deleting_a_dish_removes_it_from_those_who_adopted_it(): void
    {
        $owner = User::factory()->create();
        $dish = $this->dishOf($owner, shared: true);
        $this->actingAs($adopter = User::factory()->create());
        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk();

        $this->actingAs($owner);
        $this->deleteJson("/api/dishes/{$dish->id}")->assertNoContent();

        $this->actingAs($adopter);
        $this->getJson('/api/dishes')->assertOk()->assertJsonCount(0, 'data');
        $this->assertDatabaseCount('dish_subscriptions', 0);
    }

    public function test_shared_dishes_can_be_filtered_by_slot_and_tag(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $this->postJson('/api/dishes', $this->dishPayload(['name' => 'Soupe', 'slots' => ['dinner'], 'tagIds' => ['soup'], 'isShared' => true]));
        $this->postJson('/api/dishes', $this->dishPayload(['name' => 'Salade', 'slots' => ['lunch'], 'tagIds' => ['vegetarian'], 'isShared' => true]));

        $this->actingAs(User::factory()->create());

        $this->getJson('/api/dishes/shared?slot=dinner')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Soupe');
        $this->getJson('/api/dishes/shared?tag=vegetarian')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Salade');
        $this->getJson('/api/dishes/shared?q=sou')->assertJsonCount(1, 'data');
    }

    public function test_an_ingredient_used_by_a_dish_cannot_be_deleted(): void
    {
        $this->actingAs($owner = User::factory()->create());
        $dish = $this->dishOf($owner);
        $ingredient = $dish->lines->first()->ingredient;

        $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertStatus(409);
    }

    public function test_an_unused_ingredient_can_be_deleted_only_by_its_owner(): void
    {
        $owner = User::factory()->create();
        $ingredient = Ingredient::create(['owner_id' => $owner->id, 'name' => 'Sel', 'name_key' => 'sel']);

        $this->actingAs(User::factory()->create());
        $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertForbidden();

        $this->actingAs($owner);
        $this->deleteJson("/api/ingredients/{$ingredient->id}")->assertNoContent();
    }

    public function test_the_catalog_requires_a_session(): void
    {
        $this->getJson('/api/dishes')->assertUnauthorized();
    }

    private function dishOf(User $owner, bool $shared = false): Dish
    {
        $this->actingAs($owner);
        $id = $this->postJson('/api/dishes', $this->dishPayload(['isShared' => $shared]))->assertCreated()->json('dish.id');

        return Dish::with('lines.ingredient')->findOrFail($id);
    }
}
