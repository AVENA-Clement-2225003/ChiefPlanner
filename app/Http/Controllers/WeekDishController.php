<?php

namespace App\Http\Controllers;

use App\Models\Plats;
use App\Models\Quantitees;
use App\Models\Semaine;
use App\Models\SemainePlanif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class WeekDishController extends Controller
{
    public function inspectDish(int $day_id) {
        $day = Semaine::where('id_jour', $day_id)->first();
        $dish = SemainePlanif::where('semaine_planif.id_utilisateur', Session::get('user_id'))->where('id_jour', $day_id)->join('plats', 'plats.id_plat', '=', 'semaine_planif.id_plat')->first();
        $ingredients = Quantitees::join('ingredients', 'quantitees.id_ingredient', '=', 'ingredients.id_ingredient')
            ->where('quantitees.id_plat', $dish->id_plat)
            ->select('quantitees.quantity', 'ingredients.nom')->get();
        return view('week_dish.index', compact('dish', 'ingredients', 'day'));
    }

    public function updateDish(string $day_id) {
        return redirect()->route('week-dish.inspect')->with('success', 'Plat ');
    }

    public function regenDish(string $day_id) {
        return redirect()->route('week-dish.inspect')->with('success', 'Plat ');
    }

    public function search(Request $request)
    {
        $dish_name = $request->input('dish_name');
        $results = Plats::where('non', 'like', "%{$dish_name}%")->get();

        return response()->json($results);
    }
}
