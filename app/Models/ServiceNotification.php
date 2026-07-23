<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceNotification extends Model
{
    protected $fillable = ['service', 'affectation_id', 'iddmd', 'message', 'is_read'];
}
