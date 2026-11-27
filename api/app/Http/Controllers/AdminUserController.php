<?php

namespace App\Http\Controllers;

use App\Account\AccountDeletion;
use App\Auth\AuthentikAdminGroup;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class AdminUserController extends Controller
{
    public function __construct(private readonly AuthentikAdminGroup $adminGroup) {}

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::query()->orderBy('name')->get());
    }

    /** Erases a user's data on request (RGPD). An admin cannot erase their own account this way: DELETE /me exists. */
    public function destroy(Request $request, User $user, AccountDeletion $deletion): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'Utilisez la suppression de votre propre compte.'], 422);
        }

        $deletion->erase($user);

        return response()->json(null, 204);
    }

    public function grantAdmin(User $user): UserResource|JsonResponse
    {
        return $this->changeAdminRole($user, true);
    }

    public function revokeAdmin(Request $request, User $user): UserResource|JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json(['message' => 'Un administrateur ne peut pas se retirer son propre rôle.'], 422);
        }

        return $this->changeAdminRole($user, false);
    }

    private function changeAdminRole(User $user, bool $makeAdmin): UserResource|JsonResponse
    {
        try {
            $makeAdmin ? $this->adminGroup->add($user) : $this->adminGroup->remove($user);
        } catch (Throwable $error) {
            report($error);

            return response()->json(['message' => "Authentik n'a pas pu modifier le groupe."], 502);
        }

        $user->update(['is_admin' => $makeAdmin]);

        return new UserResource($user);
    }
}
