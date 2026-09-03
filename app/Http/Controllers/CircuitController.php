<?php

namespace App\Http\Controllers;

use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\ServiceNotification;
use App\Models\Tabdepot;
use App\Services\SmsService;
use Illuminate\Http\Request;

class CircuitController extends Controller
{
    protected SmsService $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

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
        if (! auth()->user()->canAccessDepot()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        if ($tabdepot->statut_circuit !== 'accueil') {
            abort(403, "Cette demande n'est plus à l'accueil, elle ne peut pas être renvoyée au Cabinet depuis ici.");
        }

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
     * Fatou : liste des demandes à traiter (venant de l'accueil).
     */
    public function fatouIndex()
    {
        $aEnvoyer = Tabdepot::where('statut_circuit', 'fatou')
            ->whereNull('decision_maire')
            ->orderByDesc('id')
            ->get();

        return view('circuit.fatou', compact('aEnvoyer'));
    }

    /**
     * Fatou : transmettre une demande au Maire.
     */
    public function sendToMaire(Tabdepot $tabdepot)
    {
        if ($tabdepot->statut_circuit !== 'fatou') {
            abort(403, "Cette demande n'est pas en attente chez le Cabinet, elle ne peut pas être transmise au Maire.");
        }

        $this->logHistorique($tabdepot, $tabdepot->statut_circuit, 'maire');

        $tabdepot->update(['statut_circuit' => 'maire']);

        return back()->with('success', 'La demande a été transmise au Maire.');
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
     * Maire : envoyer directement vers un service, sans passer par une décision d'accepter/refuser.
     */
    /**
     * Maire : accepter / refuser une demande (obligatoire), avec remarque et service optionnels.
     * Si un service est choisi, la demande y est envoyée directement.
     * Sinon, elle repart chez Fatou pour orientation.
     */
    public function decide(Request $request, Tabdepot $tabdepot)
    {
        if ($tabdepot->statut_circuit !== 'maire') {
            abort(403, "Cette demande n'est pas en attente de décision chez le Maire.");
        }

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

        ServiceNotification::create([
            'service' => $request->service_destination,
            'iddmd' => $tabdepot->id,
            'message' => "Nouvelle demande affectée à votre service (Code demande : {$tabdepot->id}).",
        ]);

        if ($tabdepot->tel) {
            $decisionLabel = $request->decision_maire === 'accepte' ? 'acceptée' : 'refusée';
            $this->sms->send(
                $tabdepot->tel,
                "Bonjour {$tabdepot->nom}, votre demande N°{$tabdepot->id} a été {$decisionLabel} et transmise au service {$request->service_destination}. Commune de Tevragh Zeina."
            );
        }

        return back()->with('success', 'La décision a été enregistrée et la demande envoyée au service.');
    }

    /**
     * Service : liste des demandes orientées vers le service de l'utilisateur connecté.
     */
    public function serviceIndex()
    {
        $user = auth()->user();

        $demandes = Tabdepot::where('statut_circuit', 'service')
            ->when(! $user->canAccessAllServices(), fn ($q) => $q->where('service_assigne', $user->service))
            ->orderByDesc('id')
            ->get();

        $demandesTraitees = Tabdepot::where('statut_circuit', 'cloture')
            ->when(! $user->canAccessAllServices(), fn ($q) => $q->where('service_assigne', $user->service))
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'traitees_page');

        return view('circuit.service', compact('demandes', 'demandesTraitees'));
    }

    /**
     * Service : marquer une demande comme traitée (fin du circuit).
     */
    public function closeDemande(Tabdepot $tabdepot)
    {
        $user = auth()->user();

        if (! $user->canAccessAllServices() && $tabdepot->service_assigne !== $user->service) {
            abort(403, "Cette demande n'est pas assignée à votre service.");
        }

        if ($tabdepot->statut_circuit !== 'service') {
            abort(403, "Cette demande n'est pas en attente de traitement par un service.");
        }

        $tabdepot->update(['statut_circuit' => 'cloture']);

        $this->logHistorique($tabdepot, 'service', 'cloture', 'Demande traitée par le service ' . $tabdepot->service_assigne);

        if ($tabdepot->tel) {
            $this->sms->send(
                $tabdepot->tel,
                "Bonjour {$tabdepot->nom}, votre demande N°{$tabdepot->id} a été traitée par le service {$tabdepot->service_assigne}. Vous pouvez la récupérer. Commune de Tevragh Zeina."
            );
        }

        return back()->with('success', 'La demande a été marquée comme traitée.');
    }

    /**
     * Accueil / Admin : suivi de toutes les demandes engagées dans le circuit.
     */
    public function suiviIndex(Request $request)
    {
        $search = $request->search;
        $statut = $request->statut;

        $demandes = Tabdepot::where('statut_circuit', '!=', 'accueil')
            ->when($statut, fn ($q) => $q->where('statut_circuit', $statut))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nom', 'like', "%{$search}%")
                        ->orWhere('nni', 'like', "%{$search}%")
                        ->orWhere('objet', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('id', ltrim($search, '0') ?: '0');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('circuit.suivi', compact('demandes', 'search', 'statut'));
    }

    /**
     * Accueil / Admin : détail + historique complet d'une demande.
     */
    public function historique(Tabdepot $tabdepot)
    {
        $historiques = $tabdepot->historiques()->with('user')->paginate(10);

        return view('circuit.historique', compact('tabdepot', 'historiques'));
    }
}
