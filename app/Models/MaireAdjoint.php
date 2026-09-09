<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaireAdjoint extends Model
{
    use HasFactory;
    protected $table ='maire_adjoint';
    protected $fillable = ['name'];
    protected $hidden=['created_at' ,'updated_at'];
}
