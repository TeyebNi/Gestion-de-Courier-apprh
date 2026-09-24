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

    /**
     * Filtre commun à la liste et à l'export CSV, pour que l'export ne
     * ramène que ce que l'accueil voit réellement à l'écran (recherche +
     * statut sélectionné), pas systématiquement tout.
     */
    private function applyFilters($query, ?string $search, ?string $statut)
    {
        return $query
            ->when($search, function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('objet', 'like', "%{$search}%");
            })
            ->filterByStatut($statut);
    }

    public function index(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $search = $request->search;
        $statut = $request->statut;

        $tabdepot = $this->applyFilters(Tabdepot::query(), $search, $statut)
            ->orderby('id', 'desc')
            ->paginate(5)
            ->appends(['search' => $search, 'statut' => $statut]);

        // Simple aperçu du prochain code (pas une réservation : le code réel
        // est toujours attribué à l'enregistrement, à partir de l'id auto-
        // incrémenté) — juste pour rassurer l'accueil avant de valider.
        $nextReference = sprintf('%03d', (Tabdepot::withTrashed()->max('id') ?? 0) + 1);

        return view('depot.index', compact('tabdepot', 'search', 'statut', 'nextReference'));
    }

    public function exportExcel(Request $request)
    {
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        $tabdepots = $this->applyFilters(Tabdepot::query(), $request->search, $request->statut)
            ->orderby('id', 'asc')
            ->get();

        return $this->streamCsv(
            $tabdepots,
            ['N°', 'Code', 'Objet', 'Nom', 'Téléphone', 'Origine', 'Détails Origine', 'Date réception', 'Statut'],
            fn ($t, $i) => [$i + 1, $t->reference, $t->objet, $t->nom, $t->tel, $t->origine, $t->origine_detail, $t->daterecp, $t->statutLabel()],
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

        $request->validate([
            'origine' => ['required', 'in:interne,externe'],
            'objet' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'tel' => ['nullable', 'regex:/^[234]\d{7}$/'],
            'nni' => ['nullable', 'string', 'max:30'],
            'piece_jointe' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ], [
            'origine.required' => "L'origine est obligatoire.",
            'tel.regex' => 'Le téléphone doit contenir 8 chiffres et commencer par 2, 3 ou 4.',
            'piece_jointe.required' => 'La pièce jointe est obligatoire.',
            'piece_jointe.mimes' => 'La pièce jointe doit être une image (JPG, PNG) ou un PDF.',
            'piece_jointe.max' => 'La pièce jointe ne doit pas dépasser 10 Mo.',
        ]);

        $demande = Tabdepot::create([
            'origine' => $request->origine,
            'objet' => $request->objet,
            'nom' => $request->nom,
            'tel' => $request->tel,
            'nni' => $request->nni,
            'daterecp' => now()->format('Y-m-d'),
            'piece_jointe' => $request->hasFile('piece_jointe')
                ? $request->file('piece_jointe')->store('pieces-jointes', 'public')
                : null,
        ]);

        // Le code est généré automatiquement à partir de l'id auto-incrémenté
        // (unique et sans coordination requise, même en cas de saisies
        // simultanées) plutôt que saisi manuellement par l'accueil.
        $demande->update(['reference' => sprintf('%03d', $demande->id)]);

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

        if (($tabdepot->statut_circuit ?? 'accueil') !== 'accueil') {
            abort(403, "Cette demande a déjà été envoyée dans le circuit et ne peut plus être modifiée depuis l'accueil.");
        }

        $request->validate([
            'origine' => ['required', 'in:interne,externe'],
            'objet' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'tel' => ['nullable', 'regex:/^[234]\d{7}$/'],
            'nni' => ['nullable', 'string', 'max:30'],
            'piece_jointe' => [Rule::requiredIf(! $tabdepot->piece_jointe), 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ], [
            'origine.required' => "L'origine est obligatoire.",
            'tel.regex' => 'Le téléphone doit contenir 8 chiffres et commencer par 2, 3 ou 4.',
            'piece_jointe.required' => 'La pièce jointe est obligatoire.',
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

        // Le code (reference) est généré automatiquement à la création et
        // n'est jamais modifiable depuis l'accueil.
        $tabdepot->update([
            'origine' => $request->origine,
            'objet' => $request->objet,
            'nom' => $request->nom,
            'tel' => $request->tel,
            'nni' => $request->nni,
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

