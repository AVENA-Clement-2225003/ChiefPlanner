<?php

namespace App\Planning;

use Closure;

/**
 * Random draw without repetition: a dish is used again only once every candidate of that Slot was used.
 */
class RandomSelection implements SelectionStrategy
{
    /** @param Closure(int): int|null $pickIndex returns an index below the given count (random by default) */
    public function __construct(private readonly ?Closure $pickIndex = null) {}

    public function select(array $requests): array
    {
        $used = [];
        $chosen = [];

        foreach ($requests as $request) {
            $chosen[$request->key()] = $this->draw($request->candidateDishIds, $used);
        }

        return $chosen;
    }

    /**
     * @param  list<int>  $candidates
     * @param  array<int, int>  $used  times each dish was already drawn in the week (updated)
     */
    private function draw(array $candidates, array &$used): ?int
    {
        if ($candidates === []) {
            return null;
        }

        $leastUsed = min(array_map(fn (int $id) => $used[$id] ?? 0, $candidates));
        $pool = array_values(array_filter($candidates, fn (int $id) => ($used[$id] ?? 0) === $leastUsed));
        $dishId = $pool[$this->randomIndex(count($pool))];
        $used[$dishId] = ($used[$dishId] ?? 0) + 1;

        return $dishId;
    }

    private function randomIndex(int $count): int
    {
        return $this->pickIndex ? ($this->pickIndex)($count) : random_int(0, $count - 1);
    }
}
