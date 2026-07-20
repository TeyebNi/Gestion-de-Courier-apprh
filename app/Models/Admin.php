<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
     use HasFactory;
    protected $table = 'admins';
    protected $fillable = [
        'name', 'email', 'username','password','created_at','updated_at','com_code','photo'];
    protected $hidden=['created_at' ,'updated_at'];    
}
