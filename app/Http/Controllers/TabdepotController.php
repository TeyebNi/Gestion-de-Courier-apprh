<?php

namespace App\Http\Controllers;

use App\Models\DemandeHistorique;
use App\Models\Tabdepot;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Traits\ExportsCsv;
use Illuminate\Validation\Rule;

class TabdepotController extends Controller
{
    use ExportsCsv;

    public function index(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $search = $request->search;

        $tabdepot = Tabdepot::query()
            ->when($search, function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                      ->orWhere('objet', 'like', "%{$search}%");
            })
            ->orderby('id', 'desc')
            ->paginate(5)
            ->appends(['search' => $search]);

        return view('depot.index', compact('tabdepot', 'search'));
    }

    public function exportExcel(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $tabdepots = Tabdepot::orderby('id', 'asc')->get();

        return $this->streamCsv(
            $tabdepots,
            ['N°', 'Code', 'Objet', 'Origine', 'Détails Origine', 'Date réception', 'Statut'],
            fn ($t, $i) => [$i + 1, $t->reference, $t->objet, $t->origine, $t->origine_detail, $t->daterecp, $t->statutLabel()],
            'depot_demandes'
        );
    }

    public function print_facture($idt)
    {
        // Accueil imprime ce reçu au dépôt ; le Cabinet de Maire réimprime le
        // même document pour le porter physiquement au Maire (voir decide()).
        if (! auth()->user()->canAccessDepot() && ! auth()->user()->canAccessCabinet()) {
            abort(403, "Cette page est réservée à l'accueil, au Cabinet de Maire et aux administrateurs.");
        }

        $detailf = Tabdepot::where('id', $idt)->firstOrFail();

        $html = view('depot.print_reçu', compact('detailf'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('recu_depot_' . $detailf->id . '.pdf', \Mpdf\Output\Destination::DOWNLOAD), 200)
            ->header('Content-Type', 'application/pdf');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $request->merge(['reference' => trim((string) $request->input('reference'))]);

        $request->validate([
            'origine' => ['required', 'in:interne,externe'],
            'reference' => ['required', 'string', 'max:100', Rule::unique('tabdepot', 'reference')],
            'objet' => ['nullable', 'string', 'max:255'],
            'piece_jointe' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ], [
            'origine.required' => "L'origine est obligatoire.",
            'reference.required' => 'Le code est obligatoire.',
            'reference.unique' => 'Ce code est déjà utilisé par une autre demande.',
            'piece_jointe.mimes' => 'La pièce jointe doit être une image (JPG, PNG) ou un PDF.',
            'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
        ]);

        $demande = Tabdepot::create([
            'origine' => $request->origine,
            'reference' => $request->reference,
            'objet' => $request->objet,
            'daterecp' => now()->format('Y-m-d'),
            'piece_jointe' => $request->hasFile('piece_jointe')
                ? $request->file('piece_jointe')->store('pieces-jointes', 'public')
                : null,
        ]);

        DemandeHistorique::create([
            'tabdepot_id' => $demande->id,
            'vers_statut' => 'accueil',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('depot.index')->with('success', "Demande {$demande->reference} enregistrée avec succès.");
    }

    public function update(Request $request, Tabdepot $tabdepot)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $request->merge(['reference' => trim((string) $request->input('reference'))]);

        $request->validate([
            'origine' => ['required', 'in:interne,externe'],
            'reference' => ['required', 'string', 'max:100', Rule::unique('tabdepot', 'reference')->ignore($tabdepot->id)],
            'objet' => ['nullable', 'string', 'max:255'],
            'piece_jointe' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ], [
            'origine.required' => "L'origine est obligatoire.",
            'reference.required' => 'Le code est obligatoire.',
            'reference.unique' => 'Ce code est déjà utilisé par une autre demande.',
            'piece_jointe.mimes' => 'La pièce jointe doit être une image (JPG, PNG) ou un PDF.',
            'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
        ]);

        $piece_jointe = $tabdepot->piece_jointe;
        if ($request->hasFile('piece_jointe')) {
            if ($piece_jointe) {
                Storage::disk('public')->delete($piece_jointe);
            }
            $piece_jointe = $request->file('piece_jointe')->store('pieces-jointes', 'public');
        }

        $tabdepot->update([
            'origine' => $request->origine,
            'reference' => $request->reference,
            'objet' => $request->objet,
            'piece_jointe' => $piece_jointe,
        ]);

        DemandeHistorique::create([
            'tabdepot_id' => $tabdepot->id,
            'vers_statut' => $tabdepot->statut_circuit,
            'user_id' => auth()->id(),
            'commentaire' => 'Informations de la demande modifiées par ' . auth()->user()->name . '.',
        ]);

        return redirect()->route('depot.index')->with('success', "Demande {$tabdepot->reference} modifiée avec succès.");
    }

    public function destroy(Tabdepot $tabdepot)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        if (($tabdepot->statut_circuit ?? 'accueil') !== 'accueil') {
            abort(403, "Cette demande a déjà été envoyée dans le circuit et ne peut plus être supprimée depuis l'accueil.");
        }

        $reference = $tabdepot->reference;
        $tabdepot->delete();

        return redirect()->route('depot.index')->with('success', "Demande {$reference} supprimée avec succès.");
    }

    public function trashed(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $search = $request->search;

        $tabdepot = Tabdepot::onlyTrashed()
            ->when($search, function ($query) use ($search) {
                $query->where('reference', 'like', "%{$search}%")
                      ->orWhere('objet', 'like', "%{$search}%");
            })
            ->orderByDesc('deleted_at')
            ->paginate(5)
            ->appends(['search' => $search]);

        return view('depot.corbeille', compact('tabdepot', 'search'));
    }

    public function restore($id)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $tabdepot = Tabdepot::onlyTrashed()->findOrFail($id);
        $tabdepot->restore();

        return redirect()->route('depot.trashed')->with('success', "Demande {$tabdepot->reference} restaurée avec succès.");
    }

    public function forceDelete($id)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, "Seuls les administrateurs peuvent supprimer définitivement une demande.");
        }

        $tabdepot = Tabdepot::onlyTrashed()->findOrFail($id);
        $reference = $tabdepot->reference;
        if ($tabdepot->piece_jointe) {
            Storage::disk('public')->delete($tabdepot->piece_jointe);
        }
        $tabdepot->forceDelete();

        return redirect()->route('depot.trashed')->with('success', "Demande {$reference} supprimée définitivement.");
    }
}
