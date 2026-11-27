<?php

namespace App\Shopping;

/** The total of one ingredient the week's meals call for, in base units (g, ml or items). */
final readonly class Need
{
    public function __construct(
        public int $ingredientId,
        public string $ingredientName,
        /** `mass`, `volume` or `count`. */
        public string $family,
        /** The unit of a count need (a clove is not an item); null for mass and volume. */
        public ?string $countUnitId,
        public float $base,
    ) {}

    public function key(): string
    {
        return $this->ingredientId.':'.($this->countUnitId ?? $this->family);
    }
}
