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
        'statut_circuit', 'decision_maire', 'remarque_maire', 'service_assigne',
        'created_at','updated_at'];
    protected $hidden=['created_at' ,'updated_at'];

    public function historiques()
    {
        return $this->hasMany(DemandeHistorique::class, 'tabdepot_id')->orderByDesc('created_at');
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
}
