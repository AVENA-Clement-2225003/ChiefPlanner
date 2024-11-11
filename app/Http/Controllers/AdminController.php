<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function showDashboard () {
        $roles = Role::all();
        $accounts = User::join('roles', 'utilisateurs.id_role', '=', 'roles.id_role')->get();
        return view('admin.dashboard', compact('accounts', 'roles'));
    }
    public function showUser (string $user_id) {
        $account = User::where('id_utilisateur', $user_id)->first();
        return view('admin.user.inspect', compact('account'));
    }
    public function changeRole (string $user_id) {
        // Check que l'on essaie pas de delete un autre admin
        return redirect(route('admin.dashboard'))->with('success', 'Rien n\'as été changé car le code n\'est pas fait');
    }
    public function deleteUser (string $user_id) {
        //290404 supprimer l'utilisateur en assigant ses plats et ingredient a un compte qui sert de "deleted account"
        // Check que l'on essaie pas de delete un autre admin
        return redirect(route('admin.dashboard'))->with('success', 'Rien n\'as été supprimé car le code n\'est pas fait');
    }
}
