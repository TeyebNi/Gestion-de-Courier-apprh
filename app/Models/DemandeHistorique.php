<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeHistorique extends Model
{
    use HasFactory;

    protected $table = 'demande_historiques';

    protected $fillable = [
        'tabdepot_id',
        'de_statut',
        'vers_statut',
        'user_id',
        'commentaire',
    ];

    public function tabdepot()
    {
        return $this->belongsTo(Tabdepot::class, 'tabdepot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
