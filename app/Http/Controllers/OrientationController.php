<?php

namespace App\Http\Controllers;

use App\Models\Orientation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrientationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Orientation::orderby('id', 'asc');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $client['orientation'] = $query->paginate(5)->appends(['search' => $search]);
        return view('orientation.index', $client)->with('search', $search);
    }

    public function exportPDF4()
    {
        $data = Orientation::all();
        view()->share('data', $data);
        $pdf = PDF::loadView('admin.show-pdf');
        return $pdf->download('data.pdf');
    }

    public function exportPDF()
    {
        $data = Orientation::all();
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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => "Veuillez entrer le nom de l'orientation.",
        ]);

        $orientation = Orientation::create([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "L'orientation « {$orientation->name} » a été ajoutée avec succès.",
                'name' => $orientation->name,
            ]);
        }

        session()->flash('success', 'les donnees successfully enregistre.');
        return redirect()->route('orientation.index')->with('succes', ' Orientation saved');
    }

    public function show(Orientation $orientation)
    {
        //
    }

    public function edit(Orientation $orientation)
    {
        //
    }

    public function update(Request $request, Orientation $orientation)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => "Veuillez entrer le nom de l'orientation.",
        ]);

        $orientation->update([
            'name' => $request->name,
        ]);

        return redirect()->route('orientation.index')->with('success', "Orientation #{$orientation->id} modifiée avec succès.");
    }

    public function destroy(Orientation $orientation)
    {
        $id = $orientation->id;
        $orientation->delete();

        return redirect()->route('orientation.index')->with('success', "Orientation #{$id} supprimée avec succès.");
    }
}