<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
class DetalleVenta extends Model
{
    //
    protected $fillable = [
        'cantidad', 'precioUnitario',
        'proId'       ,'opcion','descuento' 
        
    ];

    public static function queryVentaParcial($venId)
    {

        $venta_parciales = Venta::select(
                                  
                                  db::raw('((detalle_ventas.cantidad*detalle_ventas.precioUnitario)) as parcial')
                                )
                        ->leftJoin('detalle_ventas as detalle_ventas','ventas.id','=','detalle_ventas.venId')
                        ->where('ventas.id',$venId)
                      ;
        return $venta_parciales;

}


public static function queryDescuentos($venId)
{

    $venta_parciales = Venta::select(
                              
                              db::raw('((detalle_ventas.cantidad*detalle_ventas.descuento)) as descuentos')
                            )
                    ->leftJoin('detalle_ventas as detalle_ventas','ventas.id','=','detalle_ventas.venId')
                    ->where('ventas.id',$venId)
                  ;
    return $venta_parciales;

}


}