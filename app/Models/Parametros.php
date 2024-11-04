<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametros extends Model
{

    protected $primaryKey = 'id';

       protected $fillable = [
        'nombre',
        'descripcion',
        'valor'
        ];

}
