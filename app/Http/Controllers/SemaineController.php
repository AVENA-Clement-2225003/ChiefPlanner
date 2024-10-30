<?php

namespace App\Http\Controllers;

use App\Models\DaySelection;
use App\Models\Ingredient;
use App\Models\Plats;
use App\Models\Quantitees;
use App\Models\Semaine;
use App\Models\SemainePlanif;
use Illuminate\Support\Facades\Session;

class SemaineController extends Controller
{
    public function PrepareWeekArrayForHomePage() {
        $week = Semaine::all();
        $daySelected = DaySelection::where('id_utilisateur', Session::get('user_id'))->get()->pluck('id_jour')->toArray();
        $daylist = array();
        foreach ($week as $midday) {
            if (!isset($daylist[$midday->day_name])) { //La sous liste n'existe pas donc on la crée
                $daylist[$midday->day_name] = array();
            }
            if (in_array($midday->id_jour, $daySelected)) { // On ajoute pour le jour si l'aprèm ou le matin est selectionné
                /*$idMeal = SemainePlanif::where('id_utilisateur', Session::get('user_id'))->where('ID_JOUR', $midday->id)->first()->id_plat; //Id du repas associé à la demi-journée
                $meal = Plats::where('id', $idMeal)->first(); //Récupération du plat
                $listeIngredient = array();
                foreach (Quantitees::where('id_plat', $idMeal)->get() as $ingredient) {
                    $listeIngredient[] = Ingredient::where('id', $ingredient->id_ingredient)->first()->nom;
                }
                $daylist[$midday->day][$midday->day_time] = array($meal->nom, $listeIngredient);*/ #290404 A revoir avoir le nouveau fonctionnement de compte
                $daylist[$midday->day_name][$midday->day_time] = null;
            } else {
                $daylist[$midday->day_name][$midday->day_time] = null;
            }
        }
        return $daylist;
    }

    public function prepareIngredientList() {
        $ingredientList = array();
        foreach (SemainePlanif::all() as $meal) {
            foreach (Quantitees::where('id_plat', $meal->ID_PLAT)->get() as $ingredient) {
                $ingredientTmp = Ingredient::where('id', $ingredient->id_ingredient)->first();

                $exploseNewQuantity = explode(' ', $ingredient->quantity);
                if (isset($ingredientList[$ingredientTmp->nom])) {
                    if (isset($ingredientList[$ingredientTmp->nom][$exploseNewQuantity[1]])) {
                        $exploseCurrentQuantity = explode(' ', $ingredientList[$ingredientTmp->nom][$exploseNewQuantity[1]]);
                        $ingredientList[$ingredientTmp->nom][$exploseNewQuantity[1]] = implode(' ', [(float) $exploseCurrentQuantity[0] + (float) $exploseNewQuantity[0], $exploseCurrentQuantity[1]]);
                    } else {
                        $ingredientList[$ingredientTmp->nom][$exploseNewQuantity[1]] = $ingredient->quantity; //Si la sous liste qui est spécifique aux types n'existe pas alors, nous la créons
                    }

                }
                else {
                    $ingredientList[$ingredientTmp->nom] = array(); //Si l'ingrédient n'existe pas dans la liste alors, nous lui créons un emplacement
                    $ingredientList[$ingredientTmp->nom][$exploseNewQuantity[1]] = $ingredient->quantity;
                }
            }
        }
        return $ingredientList;
    }

    public function addToWeekPreviousGeneration($prev_week) {
        if (!Session::has('previous_weeks')) { //S'il n'y a pas de semaine précédente enregistrée
            Session::put('previous_weeks', [$prev_week->toArray()]);
        } else {
            $actualPrev[] = $prev_week->toArray(); //Ajout de la nouvelle semaine avec les précédentes
            Session::put('previous_weeks', $actualPrev);
        }
    }

    public function prepareAWeek() {
        $mealToPrepare = Semaine::where('selected', '1')->get();
        $this->addToWeekPreviousGeneration(SemainePlanif::all());
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
