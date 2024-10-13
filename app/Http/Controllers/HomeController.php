<?php

namespace App\Http\Controllers;

use App\Models\GroceriesPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function showHome() {
        $daylist = (new SemaineController())->PrepareWeekArrayForHomePage();
        $ingredientList = (new SemaineController())->prepareIngredientList();
        $priceList = $this->prepareGraphicData();
        return view('homepage', compact('daylist', 'ingredientList', 'priceList'));
    }

    public function prepareGraphicData() {
        $data = GroceriesPrice::orderBy('purchase_date', 'desc')->take(20)->get();

        $labels = $data->pluck('purchase_date')->map(function($date) {
            return Carbon::parse($date)->format('d D\. \o\f M\.');
        })->toArray();
        $data = $data->pluck('price')->toArray();
        return array($labels, $data);
    }

    public function showDebug() {
        $tmp = Session::get('previous_weeks');
        return view('debug', compact('tmp'));
    }

    public function resetDebug() {
        Session::flush();
        return redirect('/debug');
    }
}
