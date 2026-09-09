<?php

namespace App\Http\Controllers;

use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\ServiceNotification;
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
     * Cabinet de Maire : liste des demandes en attente d'annotations du Maire.
     * Le Maire ne se connecte jamais à l'application : le Cabinet lui porte le
     * dossier papier à la main, recueille ses annotations, et les saisit ici
     * en une seule étape (plus d'envoi séparé "au Maire" dans l'application).
     */
    public function fatouIndex()
    {
        $aEnvoyer = Tabdepot::where('statut_circuit', 'fatou')
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'a_envoyer_page');
        $orientations = Orientation::orderBy('name')->get();

        // Le Cabinet n'a pas accès à Suivi (vue globale tous services) : ce
        // second tableau lui donne uniquement la trace de son propre travail
        // d'annotation, une fois la demande sortie de la file d'attente.
        $dejaAnnotees = Tabdepot::whereNotNull('remarque_maire')
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'annotees_page');

        return view('circuit.fatou', compact('aEnvoyer', 'orientations', 'dejaAnnotees'));
    }

    /**
     * Cabinet de Maire : saisir les annotations du Maire (recueillies sur le
     * dossier papier) et, si la demande concerne un service, la lui transmettre.
     * Sans service concerné, la demande est directement clôturée.
     */
    public function decide(Request $request, Tabdepot $tabdepot)
    {
        if (! auth()->user()->canAccessCabinet()) {
            abort(403, "Cette page est réservée au Cabinet de Maire et aux administrateurs.");
        }

        if ($tabdepot->statut_circuit !== 'fatou') {
            abort(403, "Cette demande n'est pas en attente d'annotations du Maire.");
        }

        $request->validate([
            'remarque_maire' => ['required', 'string', 'max:2000'],
            'service_destination' => ['nullable', 'string', 'max:255'],
        ], [
            'remarque_maire.required' => "Les annotations du Maire sont obligatoires.",
        ]);

        $serviceDestination = $request->service_destination ?: null;

        $tabdepot->update([
            'remarque_maire' => $request->remarque_maire,
            'statut_circuit' => $serviceDestination ? 'service' : 'cloture',
            'service_assigne' => $serviceDestination,
            'vue_accueil' => false,
        ]);

        $this->logHistorique(
            $tabdepot,
            'fatou',
            $serviceDestination ? 'service' : 'cloture',
            'Annotations du Maire : ' . $request->remarque_maire
                . ($serviceDestination ? ' — Envoyée vers ' . $serviceDestination : ' — Classée sans service concerné')
        );

        if ($serviceDestination) {
            ServiceNotification::create([
                'service' => $serviceDestination,
                'iddmd' => $tabdepot->id,
                'message' => "Nouvelle demande affectée à votre service (Code demande : {$tabdepot->id}).",
            ]);
        }

        return back()->with('success', $serviceDestination
            ? 'Les annotations ont été enregistrées et la demande envoyée au service.'
            : 'Les annotations ont été enregistrées et la demande classée.');
    }

    /**
     * Service : liste des demandes orientées vers le service de l'utilisateur connecté.
     */
    public function serviceIndex()
    {
        $user = auth()->user();

        if (! $user->isAdmin() && empty($user->service)) {
            abort(403, "Cette page est réservée aux agents de service et aux administrateurs.");
        }

        $demandes = Tabdepot::where('statut_circuit', 'service')
            ->when(! $user->canAccessAllServices(), fn ($q) => $q->where('service_assigne', $user->service))
            ->orderByDesc('id')
            ->paginate(5, ['*'], 'demandes_page');

        $demandesTraitees = Tabdepot::where('statut_circuit', 'cloture')
            ->when(! $user->canAccessAllServices(), fn ($q) => $q->where('service_assigne', $user->service))
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'traitees_page');

        return view('circuit.service', compact('demandes', 'demandesTraitees'));
    }

    /**
     * Service : marquer une demande comme traitée (fin du circuit), avec le
     * type de résolution retenu par le service (traiter / classer / convoquer).
     */
    public function closeDemande(Request $request, Tabdepot $tabdepot)
    {
        $user = auth()->user();

        if (! $user->canAccessAllServices() && $tabdepot->service_assigne !== $user->service) {
            abort(403, "Cette demande n'est pas assignée à votre service.");
        }

        if ($tabdepot->statut_circuit !== 'service') {
            abort(403, "Cette demande n'est pas en attente de traitement par un service.");
        }

        $request->validate([
            'resolution' => ['required', 'in:traiter,classer,convoquer'],
        ], [
            'resolution.required' => "Veuillez choisir une résolution (Traiter, Classer ou Convoquer).",
        ]);

        $tabdepot->update([
            'statut_circuit' => 'cloture',
            'resolution_service' => $request->resolution,
        ]);

        $this->logHistorique($tabdepot, 'service', 'cloture', $tabdepot->resolutionLabel() . ' par le service ' . $tabdepot->service_assigne);

        // La demande est traitée : la notification qui l'annonçait n'a plus
        // besoin d'apparaître comme "à traiter" dans la cloche du service.
        ServiceNotification::where('iddmd', $tabdepot->id)
            ->where('service', $tabdepot->service_assigne)
            ->update(['is_read' => true]);

        return back()->with('success', 'La demande a été marquée comme traitée.');
    }

    /**
     * Accueil / Admin : suivi de toutes les demandes engagées dans le circuit.
     */
    public function suiviIndex(Request $request)
    {
        if (! auth()->user()->canAccessSuivi()) {
            abort(403, "Cette page est réservée à l'accueil et aux administrateurs.");
        }

        // Visiter Suivi vaut consultation : les annotations fraîchement saisies
        // par le Cabinet ne sont plus signalées comme "nouvelles" dans la cloche.
        Tabdepot::where('vue_accueil', false)->update(['vue_accueil' => true]);

        $search = $request->search;
        $statut = $request->statut;

        $demandes = Tabdepot::where('statut_circuit', '!=', 'accueil')
            ->when($statut, fn ($q) => $q->where('statut_circuit', $statut))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('objet', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhere('id', ltrim($search, '0') ?: '0');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(5)
            ->withQueryString();

        return view('circuit.suivi', compact('demandes', 'search', 'statut'));
    }

    /**
     * Accueil / Admin : détail + historique complet d'une demande.
     */
    public function historique(Tabdepot $tabdepot)
    {
        $user = auth()->user();
        $estSonPropreService = $tabdepot->service_assigne && $tabdepot->service_assigne === $user->service;

        if (! $user->canAccessSuivi() && ! $estSonPropreService) {
            abort(403, "Vous n'avez pas accès à l'historique de cette demande.");
        }

        $historiques = $tabdepot->historiques()->with('user')->paginate(5);

        return view('circuit.historique', compact('tabdepot', 'historiques'));
    }
}
