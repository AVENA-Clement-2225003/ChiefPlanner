<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function showHome() {
        $daylist = (new SemaineController())->PrepareWeekArrayForHomePage();
        $ingredientList = (new SemaineController())->prepareIngredientList();
        return view('homepage', compact('daylist', 'ingredientList'));
    }
}
