<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingresos extends Model
{


    protected $table = 'ingresos';
    protected $primaryKey = 'ingId';
       protected $fillable = [
        'venId',
        'tipoId', 'ingCantidad'

        ];

}
