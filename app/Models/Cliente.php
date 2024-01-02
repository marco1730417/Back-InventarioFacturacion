<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{

    public $timestamps = true;

    //
    protected $fillable = [
        'nombre', 'ruc',
        'direccion', 'telefono', 'email'
    ];
}