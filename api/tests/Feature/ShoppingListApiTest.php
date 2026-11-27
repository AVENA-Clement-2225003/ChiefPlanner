<?php

namespace Tests\Feature;

use App\Models\Dish;
use App\Models\Meal;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesDishes;
use Tests\TestCase;

class ShoppingListApiTest extends TestCase
{
    use MakesDishes;
    use RefreshDatabase;

    private function plan(User $user, Dish $dish, int $day, string $slot, int $servings): void
    {
        Meal::create(['user_id' => $user->id, 'day' => $day, 'slot' => $slot, 'dish_id' => $dish->id, 'servings' => $servings]);
    }

    /** @return array<string, mixed> the list item of the named ingredient */
    private function item(string $name): array
    {
        $items = collect($this->getJson('/api/shopping-list')->assertOk()->json('items'));

        return $items->firstWhere('name', $name) ?? $this->fail("No item named $name");
    }

    public function test_quantities_scale_with_servings_and_add_up_across_meals(): void
    {
        $this->actingAs($user = User::factory()->create());
        $curry = $this->makeDish($user, 'Curry');
        $this->addLine($curry, 'Riz', 100, 'g');
        $pasta = $this->makeDish($user, 'Pâtes');
        $this->addLine($pasta, 'Riz', 0.25, 'kg');
        $this->plan($user, $curry, 0, 'lunch', 4);
        $this->plan($user, $pasta, 1, 'lunch', 2);

        // 100 g × 4 + 250 g × 2 = 900 g
        $this->assertSame(['quantity' => 900, 'unitId' => 'g'], array_intersect_key($this->item('Riz'), ['quantity' => 1, 'unitId' => 1]));
    }

    public function test_mass_is_shown_in_kilograms_from_one_thousand_grams(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $this->addLine($dish, 'Farine', 250, 'g');
        $this->plan($user, $dish, 0, 'lunch', 6);

        $this->assertSame(1.5, $this->item('Farine')['quantity']);
        $this->assertSame('kg', $this->item('Farine')['unitId']);
    }

    public function test_volumes_merge_across_units_of_the_same_family(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $this->addLine($dish, 'Lait', 20, 'cl');
        $this->addLine($dish, 'Huile', 1, 'tbsp');
        $this->plan($user, $dish, 0, 'lunch', 2);

        $this->assertSame(400, $this->item('Lait')['quantity']);
        $this->assertSame('ml', $this->item('Lait')['unitId']);
        $this->assertSame(30, $this->item('Huile')['quantity']);
    }

    public function test_counts_are_rounded_up_and_kept_apart_per_unit(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $this->addLine($dish, 'Ail', 1.5, 'clove');
        $this->addLine($dish, 'Oeuf', 1.5, 'unit');
        $other = $this->makeDish($user, 'Autre');
        $this->addLine($other, 'Ail', 0.5, 'unit');
        $this->plan($user, $dish, 0, 'lunch', 1);
        $this->plan($user, $other, 0, 'dinner', 1);

        $items = collect($this->getJson('/api/shopping-list')->json('items'));

        $this->assertSame(2, $items->firstWhere('name', 'Oeuf')['quantity']);
        $garlic = $items->where('name', 'Ail');
        $this->assertCount(2, $garlic);
        $this->assertEqualsCanonicalizing(['clove', 'unit'], $garlic->pluck('unitId')->all());
    }

    public function test_a_meal_without_a_dish_adds_nothing(): void
    {
        $this->actingAs($user = User::factory()->create());
        Meal::create(['user_id' => $user->id, 'day' => 0, 'slot' => 'lunch', 'dish_id' => null, 'servings' => 2]);

        $this->getJson('/api/shopping-list')->assertOk()->assertJsonCount(0, 'items');
    }

    public function test_packs_give_the_combination_to_buy_and_the_leftover(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Gnocchis nuggets');
        $gnocchi = $this->addLine($dish, 'Gnocchis', 1, 'portion');
        $this->addLine($dish, 'Nuggets', 5, 'unit');
        $this->plan($user, $dish, 0, 'lunch', 1);
        $this->putJson("/api/store/{$gnocchi->id}", ['aisleId' => 'fresh', 'packs' => [
            ['unitId' => 'portion', 'size' => 2, 'price' => 1.89],
            ['unitId' => 'portion', 'size' => 3, 'price' => 2.5],
        ]])->assertOk();

        $item = $this->item('Gnocchis');

        $this->assertSame(1, $item['quantity']);
        $this->assertSame('fresh', $item['aisleId']);
        $this->assertEquals([['unitId' => 'portion', 'size' => 2, 'count' => 1, 'price' => 1.89]], $item['packs']['lines']);
        $this->assertSame(['quantity' => 1, 'unitId' => 'portion'], $item['packs']['leftover']);
        $this->assertSame(1.89, $item['estimatedPrice']);
    }

    public function test_the_estimate_adds_up_priced_items_and_counts_the_others(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $nuggets = $this->addLine($dish, 'Nuggets', 5, 'unit');
        $this->addLine($dish, 'Sel', 5, 'g');
        $this->plan($user, $dish, 0, 'lunch', 2);
        $this->putJson("/api/store/{$nuggets->id}", ['packs' => [['unitId' => 'unit', 'size' => 10, 'price' => 4.2]]])->assertOk();

        $list = $this->getJson('/api/shopping-list')->assertOk();

        $list->assertJsonPath('estimatedTotal', 4.2)->assertJsonPath('unpricedItems', 1);
    }

    public function test_a_pack_in_another_family_is_ignored(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $rice = $this->addLine($dish, 'Riz', 100, 'g');
        $this->plan($user, $dish, 0, 'lunch', 2);
        $this->putJson("/api/store/{$rice->id}", ['packs' => [['unitId' => 'l', 'size' => 1]]])->assertOk();

        $this->assertNull($this->item('Riz')['packs']);
    }

    public function test_items_can_be_ticked_and_unticked(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user);
        $this->addLine($dish, 'Riz', 100, 'g');
        $this->plan($user, $dish, 0, 'lunch', 1);
        $key = $this->item('Riz')['key'];

        $this->putJson('/api/shopping-list/ticks', ['key' => $key, 'checked' => true])->assertOk();
        $this->assertTrue($this->item('Riz')['purchased']);

        $this->putJson('/api/shopping-list/ticks', ['key' => $key, 'checked' => false])->assertOk();
        $this->assertFalse($this->item('Riz')['purchased']);
    }

    public function test_extras_are_listed_with_their_aisle(): void
    {
        $this->actingAs(User::factory()->create());

        $id = $this->postJson('/api/extras', ['label' => 'Céréales', 'aisleId' => 'grocery'])->assertCreated()->json('id');
        $this->putJson("/api/extras/$id", ['label' => 'Céréales complètes'])->assertOk();

        $this->getJson('/api/shopping-list')->assertJsonPath('extras.0.label', 'Céréales complètes');
        $this->deleteJson("/api/extras/$id")->assertNoContent();
        $this->getJson('/api/shopping-list')->assertJsonCount(0, 'extras');
    }

    public function test_extras_and_lists_are_private(): void
    {
        $extra = $this->actingAs(User::factory()->create())->postJson('/api/extras', ['label' => 'PQ'])->json('id');
        $this->actingAs(User::factory()->create());

        $this->deleteJson("/api/extras/$extra")->assertForbidden();
        $this->getJson('/api/shopping-list')->assertJsonCount(0, 'extras');
    }

    public function test_store_settings_only_concern_ingredients_the_user_cooks_with(): void
    {
        $dish = $this->makeDish(User::factory()->create(), shared: true);
        $foreign = $this->addLine($dish, 'Safran', 1, 'g');
        $this->actingAs(User::factory()->create());

        $this->putJson("/api/store/{$foreign->id}", ['packs' => []])->assertForbidden();

        $this->putJson("/api/dishes/{$dish->id}/subscription")->assertOk();
        $this->putJson("/api/store/{$foreign->id}", ['aisleId' => 'grocery', 'packs' => []])->assertOk();
        $this->getJson('/api/store')->assertJsonPath('data.0.name', 'Safran')->assertJsonPath('data.0.aisleId', 'grocery');
    }

    public function test_the_spending_summary_compares_the_month_with_the_previous_one(): void
    {
        $this->actingAs(User::factory()->create());
        $today = CarbonImmutable::today();
        $this->postJson('/api/spendings', ['amount' => 60.5, 'spentOn' => $today->toDateString()])->assertCreated();
        $this->postJson('/api/spendings', ['amount' => 30, 'spentOn' => $today->startOfMonth()->toDateString()])->assertCreated();
        $this->postJson('/api/spendings', ['amount' => 50, 'spentOn' => $today->subMonthNoOverflow()->startOfMonth()->addDays(2)->toDateString()])->assertCreated();

        $summary = $this->getJson('/api/spendings/summary')->assertOk();

        $summary->assertJsonPath('monthTotal', 90.5)->assertJsonCount(7, 'points');
        $this->assertSame(81, $summary->json('variationPercent'));
    }

    public function test_the_spending_summary_has_no_variation_without_a_previous_month(): void
    {
        $this->actingAs(User::factory()->create());

        $this->getJson('/api/spendings/summary')->assertOk()->assertJsonPath('monthTotal', 0)->assertJsonPath('variationPercent', null);
    }
}
