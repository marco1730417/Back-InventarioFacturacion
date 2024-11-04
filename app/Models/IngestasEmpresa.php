<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngestasEmpresa extends Model
{

    
    protected $table = 'ingestas_empresa';
    protected $primaryKey = 'impId';
       protected $fillable = [
        'empId',
        'tipoId'
        ];

}
