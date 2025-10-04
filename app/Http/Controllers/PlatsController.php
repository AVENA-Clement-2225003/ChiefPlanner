<?php

namespace App\Http\Controllers;

use App\Models\GroceriesPrice;
use App\Models\Ingredient;
use App\Models\Plats;
use App\Models\Quantitees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PlatsController extends Controller
{
    public function showPlats() {
        $plats = Plats::orderBy('nom', 'asc')->get();
        $ingredients = Ingredient::orderBy('nom', 'asc')->get();
        return view('plats', compact('plats', 'ingredients'));
    }

    public function showIngredients() {
        $ingredients = Ingredient::orderBy('nom', 'asc')->get();
        return view('ingredients', compact('ingredients'));
    }

    public function addDish(Request $request) {
        $request->validate([
            'plat_name' => 'required|string|max:191',
            'ingredients.*.id_ingredient' => 'required|exists:ingredients,id_ingredient',
            'ingredients.*.quantity' => 'required|string|max:191',
            'ingredients.*.type' => 'required|string|',
            'playlist_id' => 'nullable|exists:playlist,id_playlist'
        ]);

        // Create the dish
        $plat = Plats::create(['nom' => $request->plat_name, 'id_utilisateur'=>Session::get('user_id')]);
        
        // Save the ingredient and quantity associations
        foreach ($request->ingredients as $ingredient) {
            Quantitees::create([
                'id_plat' => $plat->id_plat,
                'id_ingredient' => $ingredient['id_ingredient'],
                'quantity' => $ingredient['quantity'] . ' ' . $ingredient['type']
            ]);
        }

        // Add to playlist if specified
        if ($request->has('playlist_id') && !empty($request->playlist_id)) {
            $playlist = \App\Models\Playlist::where('id_playlist', $request->playlist_id)
                ->where('id_utilisateur', Session::get('user_id'))
                ->first();
            
            if ($playlist) {
                $playlist->plats()->attach($plat->id_plat);
            }
        }

        return redirect('/plats')->with('success', 'Plat créé avec succès');
    }

    public function addIngredient(Request $request) {
        Ingredient::create([
            'nom' => $request['ingredient_name'],
            'id_utilisateur' => Session::get('user_id'),
        ]);
        return redirect('/ingredients');
    }

    public function addGroceriesPurchase(Request $request) {
        GroceriesPrice::create([
            'price' => $request->price,
            'id_utilisateur' => Session::get('user_id'),
        ]);
        return redirect('/');
    }

    public function getDishIngredients($day_id) {
        // Get the dish for this day
        $semainePlanif = \App\Models\SemainePlanif::where('id_utilisateur', Session::get('user_id'))
            ->where('id_jour', $day_id)
            ->first();
        
        if (!$semainePlanif) {
            return response()->json([]);
        }

        // Get ingredients with quantities
        $ingredients = Quantitees::where('id_plat', $semainePlanif->id_plat)
            ->join('ingredients', 'quantitees.id_ingredient', '=', 'ingredients.id_ingredient')
            ->select('quantitees.id_ingredient', 'ingredients.nom as name', 'quantitees.quantity')
            ->orderBy('ingredients.nom', 'asc')
            ->get();

        return response()->json($ingredients);
    }

    public function updateDish(Request $request, $day_id) {
        $request->validate([
            'dish_name' => 'required|string|max:191',
            'ingredients.*.id_ingredient' => 'required|exists:ingredients,id_ingredient',
            'ingredients.*.quantity' => 'required|string|max:191',
        ]);

        // Get the current dish assignment for this day
        $semainePlanif = \App\Models\SemainePlanif::where('id_utilisateur', Session::get('user_id'))
            ->where('id_jour', $day_id)
            ->first();

        if (!$semainePlanif) {
            return redirect('/')->with('error', 'Aucun plat trouvé pour ce jour');
        }

        // Update the dish name
        $plat = Plats::find($semainePlanif->id_plat);
        if ($plat) {
            $plat->nom = $request->dish_name;
            $plat->save();

            // Delete existing ingredient quantities
            Quantitees::where('id_plat', $plat->id_plat)->delete();

            // Add new ingredient quantities
            foreach ($request->ingredients as $ingredient) {
                Quantitees::create([
                    'id_plat' => $plat->id_plat,
                    'id_ingredient' => $ingredient['id_ingredient'],
                    'quantity' => $ingredient['quantity']
                ]);
            }
        }

        return redirect('/')->with('success', 'Plat modifié avec succès');
    }

    public function getPlatIngredients($dish_id) {
        // Get ingredients with quantities for a specific dish
        $ingredients = Quantitees::where('id_plat', $dish_id)
            ->join('ingredients', 'quantitees.id_ingredient', '=', 'ingredients.id_ingredient')
            ->select('quantitees.id_ingredient', 'ingredients.nom as name', 'quantitees.quantity')
            ->orderBy('ingredients.nom', 'asc')
            ->get();

        return response()->json($ingredients);
    }

    public function updatePlat(Request $request, $dish_id) {
        $request->validate([
            'dish_name' => 'required|string|max:191',
            'ingredients.*.id_ingredient' => 'required|exists:ingredients,id_ingredient',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.type' => 'required|string|max:191',
        ]);

        // Update the dish name
        $plat = Plats::find($dish_id);
        if (!$plat) {
            return redirect('/plats')->with('error', 'Plat non trouvé');
        }

        $plat->nom = $request->dish_name;
        $plat->save();

        // Delete existing ingredient quantities
        Quantitees::where('id_plat', $dish_id)->delete();

        // Add new ingredient quantities
        foreach ($request->ingredients as $ingredient) {
            $quantity = $ingredient['quantity'];
            if (isset($ingredient['type']) && !empty($ingredient['type'])) {
                $quantity .= ' ' . $ingredient['type'];
            }
            
            Quantitees::create([
                'id_plat' => $dish_id,
                'id_ingredient' => $ingredient['id_ingredient'],
                'quantity' => $quantity
            ]);
        }

        return redirect('/plats')->with('success', 'Plat modifié avec succès');
    }

    public function deletePlat($dish_id) {
        $plat = Plats::find($dish_id);
        if (!$plat) {
            return redirect('/plats')->with('error', 'Plat non trouvé');
        }

        // Delete associated ingredient quantities first
        Quantitees::where('id_plat', $dish_id)->delete();
        
        // Delete the dish
        $plat->delete();

        return redirect('/plats')->with('success', 'Plat supprimé avec succès');
    }
    
    /**
     * Update an ingredient's name
     *
     * @param Request $request
     * @param int $ingredient_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateIngredient(Request $request, $ingredient_id) {
        $request->validate([
            'ingredient_name' => 'required|string|max:191',
        ]);
        
        $ingredient = Ingredient::find($ingredient_id);
        
        if (!$ingredient) {
            return redirect('/ingredients')->with('error', 'Ingrédient non trouvé');
        }
        
        $ingredient->nom = $request->ingredient_name;
        $ingredient->save();
        
        return redirect('/ingredients')->with('success', 'Ingrédient modifié avec succès');
    }
}
