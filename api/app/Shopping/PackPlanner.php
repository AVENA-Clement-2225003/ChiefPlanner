<?php

namespace App\Shopping;

/**
 * Picks the packs to buy so that the need is covered with the smallest Leftover, then the fewest packs.
 * Works on whole base units (grams, millilitres, items).
 */
class PackPlanner
{
    private const WHOLE_NUMBER_TOLERANCE = 0.0001;

    private const MAX_PLANNED_QUANTITY = 200000;

    /**
     * @param  list<float>  $packSizes  pack sizes in base units
     * @return array{counts: array<int, int>, bought: int, leftover: int}|null counts per pack size (as given),
     *                                                                         or null when no plan is possible
     */
    public function plan(float $need, array $packSizes): ?array
    {
        $target = (int) ceil($need - self::WHOLE_NUMBER_TOLERANCE);
        $sizes = array_map(fn (float $size) => (int) round($size), $packSizes);
        if ($target <= 0 || $sizes === [] || min($sizes) < 1 || $target > self::MAX_PLANNED_QUANTITY) {
            return null;
        }

        $limit = $target + max($sizes);
        [$fewestPacks, $lastPack] = $this->reachableTotals($sizes, $limit);

        for ($total = $target; $total <= $limit; $total++) {
            if ($fewestPacks[$total] !== null) {
                return ['counts' => $this->countsFor($total, $lastPack, $sizes), 'bought' => $total, 'leftover' => $total - $target];
            }
        }

        return null;
    }

    /**
     * Coin-change table: for every total, the fewest packs that add up to it exactly, and the last pack used.
     *
     * @param  list<int>  $sizes
     * @return array{0: array<int, int|null>, 1: array<int, int|null>}
     */
    private function reachableTotals(array $sizes, int $limit): array
    {
        $fewest = array_fill(0, $limit + 1, null);
        $lastPack = array_fill(0, $limit + 1, null);
        $fewest[0] = 0;

        for ($total = 1; $total <= $limit; $total++) {
            foreach ($sizes as $index => $size) {
                $before = $total - $size;
                if ($before >= 0 && $fewest[$before] !== null && ($fewest[$total] === null || $fewest[$total] > $fewest[$before] + 1)) {
                    $fewest[$total] = $fewest[$before] + 1;
                    $lastPack[$total] = $index;
                }
            }
        }

        return [$fewest, $lastPack];
    }

    /**
     * @param  array<int, int|null>  $lastPack
     * @param  list<int>  $sizes
     * @return array<int, int>
     */
    private function countsFor(int $total, array $lastPack, array $sizes): array
    {
        $counts = array_fill(0, count($sizes), 0);
        while ($total > 0) {
            $index = $lastPack[$total];
            $counts[$index]++;
            $total -= $sizes[$index];
        }

        return $counts;
    }
}
