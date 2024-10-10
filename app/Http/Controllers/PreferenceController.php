<?php

namespace App\Http\Controllers;

use App\Models\Semaine;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function PrepareWeekArrayForPreferencePage () {
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

    public function showPreferences() {
        $daylist = $this->PrepareWeekArrayForPreferencePage();
        return view('preferences', compact('daylist'));
    }
}
