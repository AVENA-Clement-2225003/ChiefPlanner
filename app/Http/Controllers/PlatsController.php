<?php

namespace App\Http\Controllers;

use App\Models\GroceriesPrice;
use App\Models\Ingredient;
use App\Models\Plats;
use App\Models\Quantitees;
use Illuminate\Http\Request;

class PlatsController extends Controller
{
    public function showPlats() {
        $plats = Plats::orderBy('nom', 'asc')->get();
        $ingredients = Ingredient::all();
        return view('plats', compact('plats', 'ingredients'));
    }

    public function showIngredients() {
        $ingredients = Ingredient::orderBy('nom', 'asc')->get();
        return view('ingredients', compact('ingredients'));
    }

    public function addDish(Request $request) {
        $request->validate([
            'plat_name' => 'required|string|max:191',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|string|max:191',
            'ingredients.*.type' => 'required|string|'
        ]);

        // Create the dish
        $plat = Plats::create(['nom' => $request->plat_name]);

        // Save the ingredient and quantity associations
        foreach ($request->ingredients as $ingredient) {
            Quantitees::create([
                'id_plat' => $plat->id,
                'id_ingredient' => $ingredient['id'],
                'quantity' => $ingredient['quantity'] . ' ' . $ingredient['type']
            ]);
        }
        return redirect('/plats');
    }

    public function addIngredient(Request $request) {
        Ingredient::create([
            'nom' => $request['ingredient_name'],
        ]);
        return redirect('/ingredients');
    }

    public function addGroceriesPurchase(Request $request) {
        GroceriesPrice::create([
            'price' => $request->price,
        ]);
        return redirect('/');
    }
}
