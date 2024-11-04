<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursales extends Model
{

    protected $primaryKey = 'sucId';

       protected $fillable = [
        'sucNombre',
        'sucDescripcion',
        'sucUbicacion',
        'sucObservacion',
        'sucTelefono',
        'sucEstado',
        'sucCorreo',
        'empId' // La clave foránea que referencia a `empresas`
    ];

}
