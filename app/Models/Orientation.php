<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orientation extends Model
{
    use HasFactory;
    protected $table ='orientation';
    protected $fillable = ['name'];
    protected $hidden=['created_at' ,'updated_at'];
}
