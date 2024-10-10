<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Plats;
use Illuminate\Http\Request;

class PlatsController extends Controller
{
    public function showPlats() {
        $plats = Plats::all();
        return view('plats', compact('plats'));
    }

    public function showIngredients() {
        $ingredients = Ingredient::all();
        return view('ingredients', compact('ingredients'));
    }
}
