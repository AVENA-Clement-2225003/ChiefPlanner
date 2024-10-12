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

    public function updatePreferences(Request $request) {
        $schedule = $request->input('schedule');

        if ($schedule === null) { //Si aucun jour n'est sélectionné
            foreach (Semaine::all() as $midday) {
                $midday->selected = 0;
                $midday->save();
            }
        } else {
            foreach ($schedule as $dayName => $times) {
                // Update morning
                Semaine::where('day', $dayName)
                    ->where('time', 'morning')
                    ->update(['selected' => isset($times['morning']) ? 1 : 0]);

                // Update afternoon
                Semaine::where('day', $dayName)
                    ->where('time', 'afternoon')
                    ->update(['selected' => isset($times['afternoon']) ? 1 : 0]);
            }
        }

        return redirect()->back()->with('success', 'Preferences updated successfully!');
    }
}
