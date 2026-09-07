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
        'typdm', 'objet', 'reference', 'origine', 'origine_detail', 'type_expediteur', 'piece_jointe', 'nom', 'nni','tel','adresse', 'daterecp',
        'statut_circuit', 'decision_maire', 'remarque_maire', 'service_assigne'];
    protected $hidden=['created_at' ,'updated_at'];

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

    public function statutLabel(): string
    {
        return match ($this->statut_circuit) {
            'accueil' => 'À l\'accueil',
            'fatou' => $this->decision_maire ? 'Chez le Cabinet (retour du Maire)' : 'Chez le Cabinet',
            'maire' => 'Chez le Maire',
            'service' => 'Chez le service : ' . ($this->service_assigne ?? '—'),
            'cloture' => 'Clôturée (traitée par le service)',
            default => $this->statut_circuit ?? 'À l\'accueil',
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
            'fatou' => 'Cabinet',
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
