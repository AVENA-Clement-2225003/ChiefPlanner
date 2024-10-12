<?php

namespace App\Http\Controllers;

use App\Models\Plats;
use App\Models\Semaine;
use App\Models\SemainePlanif;

class SemaineController extends Controller
{
    public function PrepareWeekArrayForHomePage() {
        $week = Semaine::all();
        $daylist = array();
        foreach ($week as $midday) {
            if (!isset($daylist[$midday->day])) { //La sous liste n'existe pas donc on la crée
                $daylist[$midday->day] = array();
            }
            if ($midday->selected === 1) { // On ajoute pour le jour si l'aprèm ou le matin est selectionné
                $idMeal = SemainePlanif::where('ID_JOUR', $midday->id)->first()->ID_PLAT; //Id du repas associé à la demi-journée
                $meal = Plats::where('id', $idMeal)->first(); //Récupération du plat
                $daylist[$midday->day][$midday->time] = $meal->nom;
            } else {
                $daylist[$midday->day][$midday->time] = null;
            }
        }
        return $daylist;
    }

    public function prepareAWeek() {
        $mealToPrepare = Semaine::where('selected', '1')->get();
        $tmp = array();
        SemainePlanif::truncate(); //Remise à zéro du planning
        foreach ($mealToPrepare as $meal) {
            $newDay = new SemainePlanif;
            $newDay->ID_JOUR = $meal->id;
            $newDay->ID_PLAT = Plats::all()->random(1)->first()->id;
            $newDay->save();
        }
        return redirect('/');
    }
}
