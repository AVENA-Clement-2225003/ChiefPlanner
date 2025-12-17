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
        $dish = SemainePlanif::where('semaine_planif.id_utilisateur', Session::get('user_id'))
            ->where('id_jour', $day_id)
            ->join('plats', 'plats.id_plat', '=', 'semaine_planif.id_plat')
            ->first();

        $ingredients = $dish
            ? Quantitees::join('ingredients', 'quantitees.id_ingredient', '=', 'ingredients.id_ingredient')
                ->where('quantitees.id_plat', $dish->id_plat)
                ->select('quantitees.quantity', 'ingredients.nom')
                ->get()
            : collect();

        $navigation = $this->getDayNavigation($day_id);

        return view('week_dish.index', compact('dish', 'ingredients', 'day', 'navigation'));
    }

    private function getDayNavigation(int $currentDayId): array
    {
        $previousDay = Semaine::where('id_jour', '<', $currentDayId)
            ->orderBy('id_jour', 'desc')
            ->first();

        $nextDay = Semaine::where('id_jour', '>', $currentDayId)
            ->orderBy('id_jour', 'asc')
            ->first();

        return [
            'previous' => $previousDay?->id_jour,
            'next' => $nextDay?->id_jour,
        ];
    }

    public function updateDish(Request $request, string $day_id) {
        $dish_id = $request->input('dish_id');
        
        // Update the dish in semaine_planif
        SemainePlanif::where('id_utilisateur', Session::get('user_id'))
            ->where('id_jour', $day_id)
            ->update(['id_plat' => $dish_id]);

        return redirect()->route('week-dish.inspect', ['day_id' => $day_id])
            ->with('success', 'Plat mis à jour avec succès');
    }

    public function regenDish(string $day_id) {
        return redirect()->route('week-dish.inspect', ['day_id' => $day_id])
            ->with('success', 'Plat régénéré avec succès');
    }

    public function search(Request $request)
    {
        try {
            $dish_name = $request->input('dish_name');
            
            if (empty($dish_name)) {
                return response()->json([]);
            }
            
            $results = Plats::where('nom', 'like', "%{$dish_name}%")->get();
            
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
