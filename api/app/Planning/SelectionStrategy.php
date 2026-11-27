<?php

namespace App\Planning;

/** Decides which dish goes in each Slot of a Proposal. Replaceable without touching the rest of the planning. */
interface SelectionStrategy
{
    /**
     * @param  list<SlotRequest>  $requests
     * @return array<string, int|null> dish id per `SlotRequest::key()`, null when no dish can fill the Slot
     */
    public function select(array $requests): array;
}
