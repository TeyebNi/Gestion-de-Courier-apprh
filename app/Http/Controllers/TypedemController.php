<?php

namespace App\Http\Controllers;

use App\Models\Typedem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Traits\ExportsCsv;

class TypedemController extends Controller
{
    use ExportsCsv;

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Typedem::orderby('id', 'asc');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $client['typedem'] = $query->paginate(5)->appends(['search' => $search]);
        return view('typedem.index', $client)->with('search', $search);
    }



    /**
     * Show the form for creating a new resource.
     */
     public function exportExcel()
    {
        $typedems = Typedem::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $typedems,
            ['N°', 'Type de demande'],
            fn ($t, $i) => [$i + 1, $t->name],
            'types_demande'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('typedem', 'name')],
        ], [
            'name.required' => "Veuillez entrer le type de demande.",
            'name.unique' => "Ce type de demande existe déjà.",
        ]);

        $typedem = Typedem::create([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Le type de demande « {$typedem->name} » a été ajouté avec succès.",
                'name' => $typedem->name,
            ]);
        }

        return redirect()->route('typedem.index')->with('success', "Le type de demande « {$typedem->name} » a été ajouté avec succès.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Typedem $typedem)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('typedem', 'name')->ignore($typedem->id)],
        ], [
            'name.required' => "Veuillez entrer le type de demande.",
            'name.unique' => "Ce type de demande existe déjà.",
        ]);

        $typedem->update([
            'name' => $request->name,
        ]);

        return redirect()->route('typedem.index')->with('success', "Type de demande #{$typedem->id} modifié avec succès.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Typedem $typedem)
    {
        $id = $typedem->id;
        $typedem->delete();

        return redirect()->route('typedem.index')->with('success', "Type de demande #{$id} supprimé avec succès.");
    }
}