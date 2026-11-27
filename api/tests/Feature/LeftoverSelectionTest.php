<?php

namespace Tests\Feature;

use App\Models\User;
use App\Planning\LeftoverAwareSelection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesDishes;
use Tests\TestCase;

class LeftoverSelectionTest extends TestCase
{
    use MakesDishes;
    use RefreshDatabase;

    /** Enables only the first Slots of the week, `$count` of them, for one person each. */
    private function planOnlyFirstSlots(int $count): void
    {
        $slots = [];
        foreach (range(0, 6) as $day) {
            foreach (['lunch', 'dinner'] as $slot) {
                $slots[] = ['day' => $day, 'slot' => $slot, 'enabled' => count($slots) < $count, 'servings' => 1, 'flow' => null];
            }
        }
        $this->putJson('/api/preferences', ['slots' => $slots])->assertOk();
    }

    public function test_a_dish_is_repeated_to_use_up_what_a_pack_leaves(): void
    {
        $this->actingAs($user = User::factory()->create());
        $gnocchiDish = $this->makeDish($user, 'Gnocchis nuggets');
        $gnocchi = $this->addLine($gnocchiDish, 'Gnocchis', 1, 'portion');
        $nuggets = $this->addLine($gnocchiDish, 'Nuggets', 5, 'unit');
        $this->makeDish($user, 'Salade');
        $this->putJson("/api/store/{$gnocchi->id}", ['packs' => [['unitId' => 'portion', 'size' => 2]]])->assertOk();
        $this->putJson("/api/store/{$nuggets->id}", ['packs' => [['unitId' => 'unit', 'size' => 10]]])->assertOk();
        $this->planOnlyFirstSlots(2);

        // The random start often gives one gnocchi dish and one salad, which wastes half of each pack.
        foreach (range(1, 12) as $attempt) {
            $names = collect($this->postJson('/api/week/proposal')->json('meals'))->pluck('dishName')->sort()->values()->all();
            $this->assertNotSame(['Gnocchis nuggets', 'Salade'], $names, 'one gnocchi dish alone leaves half of each pack unused');
        }
    }

    public function test_no_dish_is_used_more_than_twice_in_the_week(): void
    {
        $this->actingAs($user = User::factory()->create());
        $dish = $this->makeDish($user, 'Gnocchis nuggets');
        $gnocchi = $this->addLine($dish, 'Gnocchis', 1, 'portion');
        $this->makeDish($user, 'Salade');
        $this->makeDish($user, 'Soupe');
        $this->putJson("/api/store/{$gnocchi->id}", ['packs' => [['unitId' => 'portion', 'size' => 4]]])->assertOk();
        $this->planOnlyFirstSlots(6);

        foreach (range(1, 8) as $attempt) {
            $names = collect($this->postJson('/api/week/proposal')->json('meals'))->pluck('dishName');

            $this->assertCount(6, $names);
            $this->assertLessThanOrEqual(LeftoverAwareSelection::MAX_REPEATS, $names->countBy()->max());
        }
    }

    public function test_without_packs_the_proposal_is_the_plain_random_one(): void
    {
        $this->actingAs($user = User::factory()->create());
        $this->makeDish($user, 'A');
        $this->makeDish($user, 'B');
        $this->planOnlyFirstSlots(2);

        $names = collect($this->postJson('/api/week/proposal')->json('meals'))->pluck('dishName')->sort()->values()->all();

        $this->assertSame(['A', 'B'], $names);
    }
}
