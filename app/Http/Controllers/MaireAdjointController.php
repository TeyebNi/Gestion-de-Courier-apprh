<?php

namespace App\Http\Controllers;

use App\Models\MaireAdjoint;
use App\Models\Tabdepot;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Traits\ExportsCsv;

class MaireAdjointController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = MaireAdjoint::orderby('id', 'asc');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $client['maireAdjoints'] = $query->paginate(5)->appends(['search' => $search]);
        return view('maire-adjoint.index', $client)->with('search', $search);
    }

    public function exportExcel()
    {
        $maireAdjoints = MaireAdjoint::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $maireAdjoints,
            ['N°', 'Adjoint au Maire'],
            fn ($m, $i) => [$i + 1, $m->name],
            'adjoints_maire'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('maire_adjoint', 'name')],
        ], [
            'name.required' => "Veuillez entrer le nom de l'adjoint au maire.",
            'name.unique' => "Cet adjoint au maire existe déjà.",
        ]);

        $maireAdjoint = MaireAdjoint::create([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "L'adjoint au maire « {$maireAdjoint->name} » a été ajouté avec succès.",
                'name' => $maireAdjoint->name,
            ]);
        }

        return redirect()->route('maire-adjoint.index')->with('success', "L'adjoint au maire « {$maireAdjoint->name} » a été ajouté avec succès.");
    }

    public function update(Request $request, MaireAdjoint $maireAdjoint)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('maire_adjoint', 'name')->ignore($maireAdjoint->id)],
        ], [
            'name.required' => "Veuillez entrer le nom de l'adjoint au maire.",
            'name.unique' => "Cet adjoint au maire existe déjà.",
        ]);

        $maireAdjoint->update([
            'name' => $request->name,
        ]);

        return redirect()->route('maire-adjoint.index')->with('success', "Adjoint au maire #{$maireAdjoint->id} modifié avec succès.");
    }

    public function destroy(MaireAdjoint $maireAdjoint)
    {
        // Le nom est référencé comme simple chaîne (pas de clé étrangère) dans
        // service_assigne et le service des comptes utilisateurs : vérifier
        // les deux avant de supprimer, sinon les enregistrements existants
        // deviennent orphelins.
        $demandesAssignees = Tabdepot::where('service_assigne', $maireAdjoint->name)->count();
        $usersUtilisant = User::where('service', $maireAdjoint->name)->count();

        if ($demandesAssignees + $usersUtilisant > 0) {
            return redirect()->route('maire-adjoint.index')->with('error', "Impossible de supprimer « {$maireAdjoint->name} » : encore utilisé par {$demandesAssignees} demande(s) et {$usersUtilisant} compte(s) utilisateur.");
        }

        $id = $maireAdjoint->id;
        $maireAdjoint->delete();

        return redirect()->route('maire-adjoint.index')->with('success', "Adjoint au maire #{$id} supprimé avec succès.");
    }
}
