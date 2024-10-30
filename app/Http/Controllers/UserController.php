<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function createNewUser(Request $request)
    {
        $newUser = new User();
        $newUser->nom = $request->name;
        $newUser->email = $request->email;
        $newUser->password = hash('sha256', $request->name);
        $newUser->save();
        return $newUser;
    }

    public function getUser(Request $request)
    {
        return User::where('email',$request->email)->first();
    }
}
