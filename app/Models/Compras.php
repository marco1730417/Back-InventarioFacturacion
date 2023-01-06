<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
    //


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'fecha', 'prvId',
        'total', 'valoriva','comprobante','descriptivo','subtotal','subtotalcero','subtotaliva','descuento'
        
        
    ];

}
