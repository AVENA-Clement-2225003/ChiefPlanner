<?php

namespace App\Http\Controllers;

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
        $daylist = array();
        foreach ($week as $midday) {
            if (!isset($daylist[$midday->day])) { //La sous liste n'existe pas donc on la crée
                $daylist[$midday->day] = array();
            }
            if ($midday->selected === 1) { // On ajoute pour le jour si l'aprèm ou le matin est selectionné
                $idMeal = SemainePlanif::where('ID_JOUR', $midday->id)->first()->ID_PLAT; //Id du repas associé à la demi-journée
                $meal = Plats::where('id', $idMeal)->first(); //Récupération du plat
                $listeIngredient = array();
                foreach (Quantitees::where('id_plat', $idMeal)->get() as $ingredient) {
                    $listeIngredient[] = Ingredient::where('id', $ingredient->id_ingredient)->first()->nom;
                }
                $daylist[$midday->day][$midday->time] = array($meal->nom, $listeIngredient);
            } else {
                $daylist[$midday->day][$midday->time] = null;
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
        $actualPrev = Session::get('previous_weeks');

        if ($actualPrev === null) {
            Session::put('previous_weeks', [$prev_week->toArray()]);
        } else {
            $actualPrev[] = $prev_week->toArray();
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
