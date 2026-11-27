<?php

namespace Tests\Unit;

use App\Shopping\PackPlanner;
use PHPUnit\Framework\TestCase;

class PackPlannerTest extends TestCase
{
    public function test_it_covers_the_need_with_the_smallest_leftover(): void
    {
        // 7 portions of gnocchi, sold by 2 or 3: 2+2+3 = 7 leaves nothing, where 4×2 would leave 1.
        $plan = (new PackPlanner)->plan(7, [2, 3]);

        $this->assertSame(0, $plan['leftover']);
        $this->assertSame(7, $plan['bought']);
        $this->assertSame(7, 2 * $plan['counts'][0] + 3 * $plan['counts'][1]);
    }

    public function test_among_equal_leftovers_it_buys_the_fewest_packs(): void
    {
        $plan = (new PackPlanner)->plan(20, [10, 20]);

        $this->assertSame([0, 1], $plan['counts']);
    }

    public function test_a_single_pack_size_rounds_up(): void
    {
        $plan = (new PackPlanner)->plan(5, [10]);

        $this->assertSame([1], $plan['counts']);
        $this->assertSame(5, $plan['leftover']);
    }

    public function test_a_fractional_need_is_rounded_up_without_counting_float_noise(): void
    {
        $this->assertSame(0, (new PackPlanner)->plan(10.00004, [10])['leftover']);
        $this->assertSame(9, (new PackPlanner)->plan(10.5, [10, 20])['leftover']);
    }

    public function test_there_is_no_plan_without_a_need_or_without_packs(): void
    {
        $this->assertNull((new PackPlanner)->plan(0, [10]));
        $this->assertNull((new PackPlanner)->plan(5, []));
    }

    public function test_it_chooses_the_exact_total_when_the_largest_pack_alone_would_waste(): void
    {
        $plan = (new PackPlanner)->plan(60, [20, 50]);

        $this->assertSame(0, $plan['leftover']);
        $this->assertSame([3, 0], $plan['counts']);
    }
}
