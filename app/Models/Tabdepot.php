<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabdepot extends Model
{
     use HasFactory;
    protected $table ='tabdepot';
    protected $fillable = [
        'typdm', 'origine', 'origine_detail', 'type_expediteur', 'piece_jointe', 'nom', 'nni','tel','adresse', 'daterecp',
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
            'fatou' => $this->decision_maire ? 'Chez Fatou (retour du Maire)' : 'Chez Fatou',
            'maire' => 'Chez le Maire',
            'service' => 'Chez le service : ' . ($this->service_assigne ?? '—'),
            'cloture' => 'Clôturée (retournée à l\'accueil)',
            default => $this->statut_circuit ?? 'À l\'accueil',
        };
    }
}
