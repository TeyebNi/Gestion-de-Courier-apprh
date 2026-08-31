<?php

namespace App\Http\Controllers;

use App\Models\Orientation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\ExportsCsv;

class OrientationController extends Controller
{
    use ExportsCsv;

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

    public function exportExcel()
    {
        $orientations = Orientation::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $orientations,
            ['N°', 'Orientation'],
            fn ($o, $i) => [$i + 1, $o->name],
            'orientations'
        );
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