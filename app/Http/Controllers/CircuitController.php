<?php

namespace App\Http\Controllers;

use App\Models\DemandeHistorique;
use App\Models\Orientation;
use App\Models\ServiceNotification;
use App\Models\Tabdepot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CircuitController extends Controller
{
    /**
     * Comptes d'un role_kind donné, avec leur titre de poste ("service", ex:
     * "Guichet Unique" ou "Conseiller chargé de l'informatique" — l'identifiant
     * de routage) et le nom de leur titulaire actuel (affichage uniquement).
     * $divisionOf filtre en plus par service parent, pour Division/Chef de
     * Service qui en dépendent.
     */
    private function titledAccounts(string $roleKind, ?string $divisionOf = null)
    {
        return User::where('role_kind', $roleKind)
            ->when($divisionOf !== null, fn ($q) => $q->where('division_of', $divisionOf))
            ->orderBy('service')
            ->get(['service', 'name'])
            ->map(fn ($u) => ['title' => $u->service, 'name' => $u->name])
            ->values();
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
            'destination_type' => null,
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

        // Adjoint au Maire route vers une personne précise sans titre de poste
        // propre : juste son nom. Conseiller a plusieurs titres distincts
        // possibles (ex: "Conseiller chargé de l'informatique") : comme
        // Division, routé par le titre, affiché avec le nom du titulaire.
        $peopleByKind = collect([
            'maire_adjoint' => User::where('role_kind', 'maire_adjoint')->orderBy('name')->pluck('name'),
            'conseiller' => $this->titledAccounts('conseiller'),
        ]);

        // Division et Chef de Service dépendent tous deux d'un service précis
        // (ex: les divisions d'Etat Civil, le Chef de Service Informatique) :
        // regroupés par nom de service pour le menu en cascade.
        $divisionsByService = $orientations->mapWithKeys(
            fn ($o) => [$o->name => $this->titledAccounts('division', $o->name)]
        );
        $chefServiceByService = $orientations->mapWithKeys(
            fn ($o) => [$o->name => User::where('role_kind', 'chef_service')->where('division_of', $o->name)->orderBy('name')->pluck('name')]
        );

        // Le Cabinet n'a pas accès à Suivi (vue globale tous services) : ce
        // second tableau lui donne uniquement la trace de son propre travail
        // d'annotation, une fois la demande sortie de la file d'attente.
        $dejaAnnotees = Tabdepot::whereNotNull('remarque_maire')
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'annotees_page');

        return view('circuit.fatou', compact('aEnvoyer', 'orientations', 'peopleByKind', 'divisionsByService', 'chefServiceByService', 'dejaAnnotees'));
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
            'destination_category' => ['nullable', Rule::in(array_merge(['service'], array_keys(User::specialServiceRoles())))],
            'destination_value' => ['nullable', 'string', 'max:255'],
        ], [
            'remarque_maire.required' => "Les annotations du Maire sont obligatoires.",
        ]);

        $category = $request->destination_category ?: null;
        $value = $request->destination_value ?: null;

        if ($category !== null) {
            // "service" (colonne) est l'identifiant de file de chaque compte à
            // la carte : le propre nom de la personne pour Adjoint au Maire/
            // Chef de Service/Conseiller, ou le nom de la division elle-même
            // (pas celui de son titulaire) pour Division.
            $validValues = $category === 'service'
                ? Orientation::pluck('name')
                : User::where('role_kind', $category)->pluck('service');

            if (! $value || ! $validValues->contains($value)) {
                $label = $category === 'service' ? 'un service' : 'une personne';
                return back()->withErrors(['destination_value' => "Veuillez choisir {$label} valide."])->withInput();
            }
        }

        $destinationType = $category;
        $destination = $category !== null ? $value : null;

        $tabdepot->update([
            'remarque_maire' => $request->remarque_maire,
            'statut_circuit' => $destination ? 'service' : 'cloture',
            'service_assigne' => $destination,
            'destination_type' => $destinationType,
            'vue_accueil' => false,
        ]);

        $this->logHistorique(
            $tabdepot,
            'fatou',
            $destination ? 'service' : 'cloture',
            'Annotations du Maire : ' . $request->remarque_maire
                . ($destination ? ' — Envoyée vers ' . $destination : ' — Classée sans destination concernée')
        );

        if ($destination) {
            ServiceNotification::create([
                'service' => $destination,
                'iddmd' => $tabdepot->id,
                'message' => "Nouvelle demande vous a été affectée (Code demande : {$tabdepot->id}).",
            ]);
        }

        return back()->with('success', $destination
            ? 'Les annotations ont été enregistrées et la demande envoyée.'
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

        $quiPrefix = match ($tabdepot->destination_type) {
            'maire_adjoint' => "l'Adjoint au Maire",
            'division' => 'la Division',
            'chef_service' => 'le Chef de Service',
            'conseiller' => 'le Conseiller',
            default => 'le service',
        };
        $qui = $quiPrefix . ' ' . $tabdepot->service_assigne;
        $this->logHistorique($tabdepot, 'service', 'cloture', $tabdepot->resolutionLabel() . ' par ' . $qui);

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
            ->filterByStatut($statut)
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
