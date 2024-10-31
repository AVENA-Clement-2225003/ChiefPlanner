<?php

namespace App\Http\Controllers;

use App\Models\DaySelection;
use App\Models\Semaine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PreferenceController extends Controller
{
    public function PrepareWeekArrayForPreferencePage () {
        $week = Semaine::all();
        $daySelected = DaySelection::where('id_utilisateur', Session::get('user_id'))->get()->pluck('id_jour')->toArray();
        $daylist = array();
        foreach ($week as $midday) {
            if (!isset($daylist[$midday->day_name])) { //La sous liste n'existe pas donc on la crée
                $daylist[$midday->day_name] = array();
            }
            if (in_array($midday->id_jour, $daySelected)) {
                $daylist[$midday->day_name][$midday->day_time] = 1;
            } else {
                $daylist[$midday->day_name][$midday->day_time] = 0;
            }
        }
        return $daylist;
    }

    public function showPreferences() {
        $daylist = $this->PrepareWeekArrayForPreferencePage();
        return view('preferences', compact('daylist'));
    }

    public function updatePreferences(Request $request) {
        $schedule = $request->input('schedule');

        DaySelection::where('id_utilisateur', Session::get('user_id'))->delete(); //On reset la selection pour ajouter que ceux selectionné
        if (isset($schedule)) {
            $user_id = Session::get('user_id');
            foreach ($schedule as $dayName => $times) {
                if (isset($times['morning'])) {
                    $id_jour = Semaine::where('day_name', $dayName)->where('day_time', 'morning')->first()->id_jour;
                    // Update morning
                    DaySelection::create(
                        [
                            'id_utilisateur' => $user_id,
                            'id_jour' => $id_jour
                        ] //Les infos à mettre en DB
                    );
                }

                if (isset($times['afternoon'])) {// Update afternoon
                    $id_jour = Semaine::where('day_name', $dayName)->where('day_time', 'afternoon')->first()->id_jour;
                    DaySelection::create(
                        [
                            'id_utilisateur' => $user_id,
                            'id_jour' => $id_jour
                        ] //Les infos à mettre en DB
                    );
                }
            }
        }


        return redirect()->back()->with('success', 'Preferences updated successfully!');
    }
}
