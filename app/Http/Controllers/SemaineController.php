<?php

namespace App\Http\Controllers;

use App\Models\Semaine;

class SemaineController extends Controller
{
    public function PrepareWeekArrayForHomePage() {
        $week = Semaine::all();
        $daylist = array();
        foreach ($week as $midday) {
            if (!isset($daylist[$midday->day])) { //La sous liste n'existe pas donc on la crée
                $daylist[$midday->day] = array();
            }
            $daylist[$midday->day][$midday->time] = $midday->selected; // On ajoute pour le jour si l'aprèm ou le matin est selectionné
        }
        return $daylist;
    }
}
