<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Typedem extends Model
{
    use HasFactory;
    protected $table ='typedem';
    protected $fillable = [
        'name','created_at','updated_at'];
    protected $hidden=['created_at' ,'updated_at'];
}
