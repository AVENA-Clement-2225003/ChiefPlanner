<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showHome() {
        $daylist = (new SemaineController())->PrepareWeekArrayForHomePage();
        return view('homepage', compact('daylist'));
    }
}
