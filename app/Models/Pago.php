<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    //


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'tipo', 
       'total',
        'pago', 
        'abono',
        'numtransf',
        'fecha', 
        'cliId', 
        'saldo'
        ,'vuelto'
        ,'tipoabono'
         ,'detalleabono'
         ,'cambioestado'
         ,'detallecontado'
    ];

}
