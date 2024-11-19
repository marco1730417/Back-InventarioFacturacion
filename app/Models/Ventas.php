<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{

    protected $primaryKey = 'venId';


    protected $fillable = [
        'venManoObra',
        'venMateriaPrima',
        'venEmpaques',
        'venObservacion'

    ];
}
