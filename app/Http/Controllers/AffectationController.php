<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Tabdepot;
use App\Models\Orientation;
use App\Models\ServiceNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AffectationController extends Controller
{
    public function index(Request $request)
    {
        $orientation = Orientation::all();
        $tabdepot = Tabdepot::all();
        $data = $request->all();
        $client['affectation'] = Affectation::orderby('id', 'asc')->paginate(50);
        return view('affectation.index', $client)->with('tabdepot', $tabdepot)->with('orientation', $orientation);
    }

    public function exportPDF4()
    {
        $data = Tabdepot::all();
        view()->share('data', $data);
        $pdf = PDF::loadView('admin.show-pdf');
        return $pdf->download('data.pdf');
    }

    public function exportPDF()
    {
        $data = Tabdepot::all();
        $pdf = Pdf::loadView('invoice', ['data' => $data]);
        return $pdf->download('invoice.pdf');
    }

    public function exportPDF1()
    {
        $pdf = Pdf::loadView('report', [
            'title' => 'Rapport de Test',
            'author' => 'Mohamed'
        ]);
        return $pdf->download('report.pdf');
    }

    public function store(Request $request)
    {
        $affectation = Affectation::create([
            'sevice' => $request->sevice,
            'dateaff' => $request->dateaff,
            'iddmd' => $request->iddmd,
        ]);

        ServiceNotification::create([
            'service' => $affectation->sevice,
            'affectation_id' => $affectation->id,
            'iddmd' => $affectation->iddmd,
            'message' => "Nouvelle demande affectée au service {$affectation->sevice} (Code demande : {$affectation->iddmd}).",
        ]);

        session()->flash('success', 'les donnees successfully enregistre.');

        return redirect()->route('affectation.index')->with('succes', ' Affectation saved');
    }

    public function show(Tabdepot $tabdepot)
    {
        //
    }

    public function edit(Tabdepot $tabdepot)
    {
        //
    }

    public function create()
    {
        //
    }

    public function update(Request $request, Affectation $affectation)
    {
        //
    }

    public function destroy(Affectation $affectation)
    {
        //
    }
}
