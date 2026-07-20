<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tabdepot extends Model
{
     use HasFactory;
    protected $table ='tabdepot';
    protected $fillable = [
        'typdm', 'nom', 'nni','tel','adresse', 'daterecp','created_at','updated_at'];
    protected $hidden=['created_at' ,'updated_at'];
}
