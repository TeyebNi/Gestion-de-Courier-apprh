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
        if (!auth()->user()->isAdmin() && empty(auth()->user()->service)) {
            abort(403, "Cette page est réservée aux administrateurs et aux utilisateurs rattachés à un service.");
        }

        $search = $request->input('search');
        $orientation = Orientation::all();
        $tabdepot = Tabdepot::all();

        $query = Affectation::orderby('id', 'asc');

        if (!auth()->user()->isAdmin()) {
            $query->where('sevice', auth()->user()->service);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sevice', 'like', "%{$search}%")
                  ->orWhere('iddmd', 'like', "%{$search}%");
            });
        }
        $client['affectation'] = $query->paginate(5)->appends(['search' => $search]);
        return view('affectation.index', $client)->with('tabdepot', $tabdepot)->with('orientation', $orientation)->with('search', $search);
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
        if (!auth()->user()->isAdmin()) {
            abort(403, "Seuls les administrateurs peuvent créer une affectation.");
        }

        $request->validate([
            'sevice' => ['required', 'string'],
            'iddmd' => ['required'],
            'dateaff' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'sevice.required' => "Veuillez sélectionner l'orientation (service).",
            'iddmd.required' => 'Veuillez sélectionner la demande.',
            'dateaff.required' => 'Veuillez entrer une date.',
            'dateaff.before_or_equal' => "La date ne peut pas être dans le futur.",
        ]);

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Affectation créée avec succès vers le service {$affectation->sevice} (Demande #{$affectation->iddmd}).",
            ]);
        }

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
        $request->validate([
            'sevice' => ['required', 'string'],
            'iddmd' => ['required'],
            'dateaff' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'sevice.required' => "Veuillez sélectionner l'orientation (service).",
            'iddmd.required' => 'Veuillez sélectionner la demande.',
            'dateaff.required' => 'Veuillez entrer une date.',
            'dateaff.before_or_equal' => "La date ne peut pas être dans le futur.",
        ]);

        $affectation->update([
            'sevice' => $request->sevice,
            'dateaff' => $request->dateaff,
            'iddmd' => $request->iddmd,
        ]);

        return redirect()->route('affectation.index')->with('success', "Affectation #{$affectation->id} modifiée avec succès.");
    }

    public function destroy(Affectation $affectation)
    {
        $id = $affectation->id;
        $affectation->delete();

        return redirect()->route('affectation.index')->with('success', "Affectation #{$id} supprimée avec succès.");
    }
}
