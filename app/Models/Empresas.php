<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{

    protected $primaryKey = 'empId';

       protected $fillable = [
        'empNombre',
        'empDescripcion',
        'empUbicacion',
        'empCorreo',
        'empEstado',
        'empTelefono',
        'empObservaciones'
    ];
    
    public function sucursales()
    {
        return $this->hasMany(Sucursales::class, 'empId', 'empId');
    }

    public function ingestas()
    {
        return $this->hasMany(IngestasEmpresa::class, 'empId', 'empId');
    }

}
