<?php

namespace App\Http\Controllers;

use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\Tabdepot;
use Illuminate\Http\Request;

class CircuitController extends Controller
{
    /**
     * Enregistre une étape dans l'historique de la demande.
     */
    private function logHistorique(Tabdepot $tabdepot, ?string $de, string $vers, ?string $commentaire = null): void
    {
        DemandeHistorique::create([
            'tabdepot_id' => $tabdepot->id,
            'de_statut' => $de,
            'vers_statut' => $vers,
            'user_id' => auth()->id(),
            'commentaire' => $commentaire,
        ]);
    }

    /**
     * Accueil : envoyer une demande vers Fatou.
     */
    public function sendToFatou(Tabdepot $tabdepot)
    {
        $ancien = $tabdepot->statut_circuit;

        $tabdepot->update([
            'statut_circuit' => 'fatou',
            'decision_maire' => null,
            'remarque_maire' => null,
            'service_assigne' => null,
        ]);

        $this->logHistorique($tabdepot, $ancien, 'fatou');

        return back()->with('success', 'La demande a été envoyée au Cabinet.');
    }

    /**
     * Fatou : liste des demandes à traiter (venant de l'accueil, ou revenues du Maire).
     */
    public function fatouIndex()
    {
        $aEnvoyer = Tabdepot::where('statut_circuit', 'fatou')
            ->whereNull('decision_maire')
            ->orderByDesc('id')
            ->get();

        $revenuesDuMaire = Tabdepot::where('statut_circuit', 'fatou')
            ->whereNotNull('decision_maire')
            ->orderByDesc('id')
            ->get();

        $orientations = Orientation::orderBy('name')->get();

        return view('circuit.fatou', compact('aEnvoyer', 'revenuesDuMaire', 'orientations'));
    }

    /**
     * Fatou : transmettre une demande au Maire.
     */
    public function sendToMaire(Tabdepot $tabdepot)
    {
        $this->logHistorique($tabdepot, $tabdepot->statut_circuit, 'maire');

        $tabdepot->update(['statut_circuit' => 'maire']);

        return back()->with('success', 'La demande a été transmise au Maire.');
    }

    /**
     * Fatou : après décision du Maire, orienter vers l'accueil (si pas de remarque)
     * ou vers un service précis (si remarque).
     */
    public function routeAfterMaire(Request $request, Tabdepot $tabdepot)
    {
        if (! empty($tabdepot->remarque_maire)) {
            $request->validate([
                'service_assigne' => ['required', 'string', 'max:255'],
            ]);

            $tabdepot->update([
                'statut_circuit' => 'service',
                'service_assigne' => $request->service_assigne,
            ]);

            $this->logHistorique($tabdepot, 'fatou', 'service', 'Orientée vers ' . $request->service_assigne);

            return back()->with('success', 'La demande a été orientée vers le service.');
        }

        $tabdepot->update([
            'statut_circuit' => 'cloture',
        ]);

        $this->logHistorique($tabdepot, 'fatou', 'cloture', 'Retournée à l\'accueil (aucune remarque)');

        return back()->with('success', 'La demande a été retournée à l\'accueil.');
    }

    /**
     * Maire : liste des demandes à décider.
     */
    public function maireIndex()
    {
        $demandes = Tabdepot::where('statut_circuit', 'maire')->orderByDesc('id')->get();
        $orientations = Orientation::orderBy('name')->get();

        return view('circuit.maire', compact('demandes', 'orientations'));
    }

    /**
     * Maire : historique des demandes déjà décidées (accepté/refusé), avec leurs remarques.
     */
    public function maireHistoriqueIndex(Request $request)
    {
        $search = $request->search;

        $demandes = Tabdepot::whereNotNull('decision_maire')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('nni', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('circuit.maire-historique', compact('demandes', 'search'));
    }

    /**
     * Maire : envoyer directement vers un service, sans passer par une décision d'accepter/refuser.
     */
    /**
     * Maire : accepter / refuser une demande (obligatoire), avec remarque et service optionnels.
     * Si un service est choisi, la demande y est envoyée directement.
     * Sinon, elle repart chez Fatou pour orientation.
     */
    public function decide(Request $request, Tabdepot $tabdepot)
    {
        $request->validate([
            'decision_maire' => ['required', 'in:accepte,refuse'],
            'remarque_maire' => ['nullable', 'string', 'max:2000'],
            'service_destination' => ['required', 'string', 'max:255'],
        ]);

        $tabdepot->update([
            'decision_maire' => $request->decision_maire,
            'remarque_maire' => $request->remarque_maire,
            'statut_circuit' => 'service',
            'service_assigne' => $request->service_destination,
        ]);

        $this->logHistorique(
            $tabdepot,
            'maire',
            'service',
            'Décision : ' . ($request->decision_maire === 'accepte' ? 'Acceptée' : 'Refusée')
                . ($request->remarque_maire ? ' — ' . $request->remarque_maire : '')
                . ' — Envoyée vers ' . $request->service_destination
        );

        return back()->with('success', 'La décision a été enregistrée et la demande envoyée au service.');
    }

    /**
     * Service : liste des demandes orientées vers le service de l'utilisateur connecté.
     */
    public function serviceIndex()
    {
        $user = auth()->user();

        $demandes = Tabdepot::where('statut_circuit', 'service')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('service_assigne', $user->service))
            ->orderByDesc('id')
            ->get();

        return view('circuit.service', compact('demandes'));
    }

    /**
     * Accueil / Admin : suivi de toutes les demandes engagées dans le circuit.
     */
    public function suiviIndex(Request $request)
    {
        $search = $request->search;

        $demandes = Tabdepot::where('statut_circuit', '!=', 'accueil')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('nni', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('id', ltrim($search, '0') ?: '0');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('circuit.suivi', compact('demandes', 'search'));
    }

    /**
     * Accueil / Admin : détail + historique complet d'une demande.
     */
    public function historique(Tabdepot $tabdepot)
    {
        $tabdepot->load('historiques.user');

        return view('circuit.historique', compact('tabdepot'));
    }
}
