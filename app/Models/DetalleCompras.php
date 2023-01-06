<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompras extends Model
{
    //


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'comId', 'proId',
        'cantidad', 'precio'
        
        
    ];

}
