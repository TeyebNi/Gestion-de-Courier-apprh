<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remarque extends Model
{
    use HasFactory;
    protected $table = 'remarque';
    protected $fillable = [
        'PersonID', 'nni', 'nom', 'fonction', 'typecontrat', 'tel', 'datenaiss', 'lieuness', 'debutcontrat', 'fincontrat','usermodif', 'obsv', 'statut','created_at','updated_at'];
    protected $hidden=['created_at' ,'updated_at'];
}
