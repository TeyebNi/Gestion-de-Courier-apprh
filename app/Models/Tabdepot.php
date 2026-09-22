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
        'objet', 'reference', 'origine', 'origine_detail', 'type_expediteur', 'piece_jointe', 'nom', 'nni', 'nif', 'tel','adresse', 'daterecp',
        'statut_circuit', 'decision_maire', 'remarque_maire', 'service_assigne', 'destination_type', 'resolution_service', 'vue_accueil'];
    protected $hidden=['created_at' ,'updated_at'];

    protected function casts(): array
    {
        return [
            'vue_accueil' => 'boolean',
        ];
    }

    public function historiques()
    {
        return $this->hasMany(DemandeHistorique::class, 'tabdepot_id')->orderByDesc('created_at');
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
                . ' : ' . ($this->service_assigne ?? '—'),
            'cloture' => 'Clôturée (' . ($this->resolutionLabel() ?: 'traitée par le service') . ')'
                . ($this->service_assigne
                    ? ' — ' . (self::CLOTURE_PAR_PREFIXES[$this->destination_type] ?? 'par le service') . ' : ' . $this->service_assigne
                    : ''),
            default => $this->statut_circuit ?? 'À l\'accueil',
        };
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
