<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tabdepot extends Model
{
     use HasFactory, SoftDeletes;
    protected $table ='tabdepot';
    protected $fillable = [
        'objet', 'reference', 'origine', 'origine_detail', 'type_expediteur', 'piece_jointe', 'nom', 'nni', 'tel','adresse', 'daterecp',
        'statut_circuit', 'decision_maire', 'remarque_maire', 'service_assigne', 'destination_type', 'resolution_service', 'vue_accueil'];
    protected $hidden=['created_at' ,'updated_at'];

    protected function casts(): array
    {
        return [
            'vue_accueil' => 'boolean',
        ];
    }

    /**
     * Filtre par statut réutilisé partout où un tri par étape du circuit est
     * proposé (Gestion des Demandes, Suivi des Demandes) : "service" regroupe
     * service/division/chef de service (routage vers un département), par
     * opposition à Adjoint au Maire/Conseiller (routage vers une personne).
     * Les trois catégories incluent aussi bien les courriers encore en cours
     * (statut_circuit "service") que déjà clôturés pour cette même catégorie
     * — pour retrouver tout ce qui concerne un service/Adjoint/Conseiller en
     * une seule recherche, qu'il soit terminé ou non. "Clôturée" reste un
     * filtre à part, pour voir tout ce qui est terminé tous destinataires
     * confondus.
     */
    public function scopeFilterByStatut($query, ?string $statut)
    {
        if (! $statut) {
            return $query;
        }

        // "destination_type" absent (NULL) ne vaut "service" que tant que le
        // courrier y est encore (vieilles données sans ce champ) — une fois
        // clôturé sans avoir jamais été routé (Cabinet "classer directement"),
        // ce NULL ne doit pas le faire apparaître comme "chez un service".
        $serviceDestinationTypes = ['service', 'division', 'chef_service'];

        return match ($statut) {
            'service' => $query->where(fn ($q) => $q
                ->where(fn ($qq) => $qq->where('statut_circuit', 'service')
                    ->where(fn ($qqq) => $qqq->whereNull('destination_type')->orWhereIn('destination_type', $serviceDestinationTypes)))
                ->orWhere(fn ($qq) => $qq->where('statut_circuit', 'cloture')->whereIn('destination_type', $serviceDestinationTypes))),
            'maire_adjoint', 'conseiller' => $query->where(fn ($q) => $q->whereIn('statut_circuit', ['service', 'cloture'])->where('destination_type', $statut)),
            default => $query->where('statut_circuit', $statut),
        };
    }

    public function historiques()
    {
        return $this->hasMany(DemandeHistorique::class, 'tabdepot_id')->orderByDesc('created_at');
    }

    /**
     * L'entrée d'historique du dépôt initial ("→ accueil"), pour retrouver
     * quel agent Accueil a enregistré cette demande — utile maintenant que
     * plusieurs comptes Accueil distincts coexistent.
     */
    public function depotHistorique()
    {
        return $this->hasOne(DemandeHistorique::class, 'tabdepot_id')->where('vers_statut', 'accueil')->oldest();
    }

    /**
     * Nom de l'agent Accueil qui a enregistré cette demande, ou null si
     * l'information n'est pas disponible (donnée historique, ou compte
     * supprimé depuis).
     */
    public function agentAccueil(): ?string
    {
        return $this->depotHistorique?->user?->name;
    }

    /**
     * Date de réception affichée au format local, tolérante à une valeur
     * historique mal formée plutôt que de faire planter la page.
     */
    public function daterecpFormatted(): string
    {
        if (! $this->daterecp) {
            return '—';
        }

        try {
            return \Carbon\Carbon::parse($this->daterecp)->format('d/m/Y');
        } catch (\Exception $e) {
            return $this->daterecp;
        }
    }

    /**
     * Préfixe (avec article) pour chaque catégorie de destination individuelle
     * — la grammaire du français diffère par genre, donc pas de règle générique.
     * Complété par ": {nom de la personne}", comme "Chez le service : X".
     */
    private const DESTINATION_PREFIXES = [
        'maire_adjoint' => "Chez l'Adjoint au Maire",
        'division' => 'Chez la Division',
        'chef_service' => 'Chez le Chef de Service',
        'conseiller' => 'Chez le Conseiller',
    ];

    /**
     * Variante "par ..." des mêmes préfixes, utilisée une fois la demande
     * clôturée pour préciser qui l'a traitée (au lieu de "chez qui" elle se trouve).
     */
    private const CLOTURE_PAR_PREFIXES = [
        'maire_adjoint' => "par l'Adjoint au Maire",
        'division' => 'par la Division',
        'chef_service' => 'par le Chef de Service',
        'conseiller' => 'par le Conseiller',
    ];

    public function statutLabel(): string
    {
        return match ($this->statut_circuit) {
            'accueil' => 'À l\'accueil',
            'fatou' => 'Chez le Cabinet de Maire',
            'maire' => 'Chez le Maire',
            'service' => (self::DESTINATION_PREFIXES[$this->destination_type] ?? 'Chez le service')
                . ' : ' . ($this->service_assigne ?? '—')
                . ($this->titulaireActuel() ? ' (' . $this->titulaireActuel() . ')' : '')
                . ($this->serviceParent() ? ' — Service : ' . $this->serviceParent() : ''),
            'cloture' => 'Clôturée (' . ($this->resolutionLabel() ?: 'traitée par le service') . ')'
                . ($this->service_assigne
                    ? ' — ' . (self::CLOTURE_PAR_PREFIXES[$this->destination_type] ?? 'par le service') . ' : ' . $this->service_assigne
                        . ($this->titulaireActuel() ? ' (' . $this->titulaireActuel() . ')' : '')
                        . ($this->serviceParent() ? ' — Service : ' . $this->serviceParent() : '')
                    : ''),
            default => $this->statut_circuit ?? 'À l\'accueil',
        };
    }

    /**
     * Nom du titulaire actuel d'une Division ou d'un poste de Conseiller —
     * ces deux rôles routent par titre de poste (ex: "Guichet Unique"), pas
     * par nom de personne, donc le titre seul ne dit pas qui traite le
     * courrier. Affiché en complément partout où le statut est montré
     * (Accueil, Suivi, Cabinet...). L'Adjoint au Maire et le Chef de Service
     * n'en ont pas besoin : leur file est déjà identifiée par leur propre nom.
     */
    public function titulaireActuel(): ?string
    {
        if (! in_array($this->destination_type, ['division', 'conseiller'], true) || ! $this->service_assigne) {
            return null;
        }

        return User::where('role_kind', $this->destination_type)
            ->where('service', $this->service_assigne)
            ->value('name');
    }

    /**
     * Service (département) dont dépend une Division ou un Chef de Service :
     * contrairement à un service "racine", les deux routent vers un
     * sous-ensemble d'un département précis (division_of), que le libellé de
     * statut doit préciser — pour qu'un filtre "Chez un service" regroupant
     * tout le monde (service/Division/Chef de Service) reste lisible : on
     * sait aussi bien de qui il s'agit que de quel service il dépend.
     * L'Adjoint au Maire et le Conseiller n'en ont pas besoin, transversaux.
     */
    public function serviceParent(): ?string
    {
        if (! in_array($this->destination_type, ['division', 'chef_service'], true) || ! $this->service_assigne) {
            return null;
        }

        return User::where('role_kind', $this->destination_type)
            ->where('service', $this->service_assigne)
            ->value('division_of');
    }

    /**
     * Libellé de la résolution choisie par le service à la clôture
     * (traiter/classer/convoquer), réutilisé partout où le statut est affiché.
     */
    public function resolutionLabel(): ?string
    {
        return match ($this->resolution_service) {
            'traiter' => 'Traitée',
            'classer' => 'Classée',
            'convoquer' => 'Convoquée',
            default => null,
        };
    }

    /**
     * Libellé court d'une étape du circuit, pour l'affichage de l'historique
     * des transferts (ex: "Fatou → Maire" doit s'afficher "Cabinet → Maire").
     */
    public static function circuitStepLabel(?string $step): string
    {
        return match ($step) {
            'accueil' => 'Accueil',
            'fatou' => 'Cabinet de Maire',
            'maire' => 'Maire',
            'service' => 'Service',
            'cloture' => 'Clôturée',
            default => $step ? ucfirst($step) : '',
        };
    }

    /**
     * Classe de badge Bootstrap associée à une étape du circuit, réutilisée
     * partout où le statut est affiché (Gestion des Demandes, Suivi, Historique).
     */
    public static function circuitStepBadgeClass(?string $step): string
    {
        return match ($step) {
            'fatou' => 'badge-info',
            'maire' => 'badge-warning',
            'service' => 'badge-primary',
            'cloture' => 'badge-success',
            default => 'badge-secondary',
        };
    }
}
