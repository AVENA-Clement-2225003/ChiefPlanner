<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ExtraController extends Controller
{
    public function showExtraEdit() {
        $extraList = Extra::where('id_utilisateur', Session::get('user_id'))->get();
        return view('extra.index', compact('extraList'));
    }

    public function addExtra(Request $request) {
        $newExtra = new Extra();
        $newExtra->intitule = $request->intitule;
        $newExtra->quantite = $request->quantite;
        $newExtra->id_utilisateur = Session::get('user_id');
        $newExtra->save();
        return redirect()->route('extra.homepage')->with('success','Extra ajouté');
    }

    public function deleteExtra(Request $request) {
        Extra::where('id_utilisateur', Session::get('user_id'))->where('intitule', $request->intitule)->first()->delete();
        return redirect()->route('extra.homepage')->with('success','Extra supprimé');
    }

    public function modifyExtra(Request $request) {
        $extra = Extra::where('id_utilisateur', Session::get('user_id'))->where('intitule', $request->intitule)->first();
        $extra->quantite = $request->newQuantite;
        return redirect()->route('extra.homepage')->with('success','Extra supprimé');
    }
}
