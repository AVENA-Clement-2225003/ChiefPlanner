<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Grants or revokes the admin role by editing the admin group in Authentik (ADR 0002).
 * The service token behind it must only be allowed to edit that one group.
 */
class AuthentikAdminGroup
{
    public function add(User $user): void
    {
        $this->request()
            ->post($this->groupUrl('add_user'), ['pk' => $this->authentikUserId($user)])
            ->throw();
    }

    public function remove(User $user): void
    {
        $this->request()
            ->post($this->groupUrl('remove_user'), ['pk' => $this->authentikUserId($user)])
            ->throw();
    }

    private function authentikUserId(User $user): int
    {
        $results = $this->request()
            ->get($this->apiUrl('core/users/'), ['email' => $user->email])
            ->throw()
            ->json('results', []);

        foreach ($results as $candidate) {
            if (strcasecmp((string) ($candidate['email'] ?? ''), $user->email) === 0) {
                return (int) $candidate['pk'];
            }
        }

        throw new OidcException('No Authentik user found for '.$user->email);
    }

    private function request(): PendingRequest
    {
        return Http::withToken((string) config('oidc.authentik.api_token'))->acceptJson();
    }

    private function groupUrl(string $action): string
    {
        return $this->apiUrl('core/groups/'.config('oidc.authentik.admin_group_id').'/'.$action.'/');
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('oidc.authentik.api_url'), '/').'/'.$path;
    }
}
