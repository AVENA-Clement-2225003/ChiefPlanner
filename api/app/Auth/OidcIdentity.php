<?php

namespace App\Auth;

/** What ChiefPlanner keeps from a verified Authentik ID token. */
final readonly class OidcIdentity
{
    /** @param list<string> $groups */
    public function __construct(
        public string $sub,
        public string $email,
        public string $name,
        public array $groups,
    ) {}

    public function isInGroup(string $group): bool
    {
        return in_array($group, $this->groups, true);
    }
}
