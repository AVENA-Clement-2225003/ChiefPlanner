<?php

namespace App\Planning;

/** One Slot of the week to fill, with the dishes it may be filled with. */
final readonly class SlotRequest
{
    /** @param list<int> $candidateDishIds */
    public function __construct(
        public int $day,
        public string $slot,
        public int $servings,
        public array $candidateDishIds,
    ) {}

    public function key(): string
    {
        return $this->day.':'.$this->slot;
    }
}
