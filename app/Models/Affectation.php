<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
     use HasFactory;
    protected $table ='affectation';
    protected $fillable = [
        'sevice', 'dateaff','iddmd','created_at','updated_at'];
    protected $hidden=['created_at' ,'updated_at'];

    public function demande()
    {
        return $this->belongsTo(Tabdepot::class, 'iddmd', 'id');
    }
}