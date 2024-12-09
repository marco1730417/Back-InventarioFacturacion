<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Sucursales;
use App\Models\Ventas;

use Illuminate\Support\Facades\DB;
use App\Models\IngestasEmpresa;
use App\Models\Ingresos;


use Auth;
use Validator;
use DateTime;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class VentasController extends ApiResponseController
{
    public function obtenerRegistros($fecha)
    {

        $cqlData = Ventas::where('venFecha',$fecha)->first();

        return $this->successResponse($cqlData, 200, 'Consulta exitosa');

            }

    public function ingestasEmpresa($empId)
    {
        $cqlData = IngestasEmpresa::where('empId',$empId)
            ->leftJoin('parametros as par', 'ingestas_empresa.tipoId', '=', 'par.id')
            ->get();
        return $this->successResponse($cqlData, 200, 'Consulta exitosa');
    }

    public function guardarRegistro(Request $request)
    {

        $costo = ( $request->venManoObra +  $request->venMateriaPrima +  $request->venEmpaques );

        $new_data = new Ventas;
        $new_data->venManoObra = $request->venManoObra;
        $new_data->venMateriaPrima = $request->venMateriaPrima;
        $new_data->venEmpaques = $request->venEmpaques;
        $new_data->venObservacion = $request->venObservacion;
        $new_data->venFecha = $request->venFecha;
        $new_data->venCosto = $costo;
        $new_data->venEstado =  1;

        $new_data->save();

        return $this->successResponse($new_data, 200, 'Registro guardado exitosamente');
    }

    public function guardarDetalleVenta(Request $request)
    {
        $sucId= $request['sucId'];
        $fecha= $request['fecha'];

            $venta= Ventas::where('venFecha',$fecha)->first();

                if(!$venta) {
                $venta = new Ventas;
                $venta->venManoObra = 0;
                $venta->venMateriaPrima = 0;
                $venta->venEmpaques = 0;
                $venta->venFecha = $fecha;
                $venta->venCosto =0;
                $venta->venEstado =  1;
                $venta->save();
            }

        $verifica_ingreso = Ingresos::where('venId', $venta->venId)->where('sucId', $sucId)->count();
        $datos_ingreso = Ingresos::where('venId', $venta->venId)->where('sucId', $sucId)->get();

        if($verifica_ingreso == 0){

        foreach ($request['cantidades'] as $tipoId => $cantidad) {

            $new_data = new Ingresos;
            $new_data->tipoId = $tipoId;
            $new_data->ingCantidad = $cantidad;
            $new_data->sucId = $sucId;
            $new_data->venId = $venta->venId;
            $new_data->save();

        }

            $this->almacenarExtras($fecha);
            return $this->successResponse($new_data, 200, 'Registro guardado exitosamente');

            }

            if($verifica_ingreso>0){

                $datos_ingreso->each(function ($item) {
                    $elemento = Ingresos::findOrFail($item->ingId);
                    $elemento->delete();
                });

                foreach ($request['cantidades'] as $tipoId => $cantidad) {

                    $new_data = new Ingresos;
                    $new_data->tipoId = $tipoId;
                    $new_data->ingCantidad = $cantidad;
                    $new_data->sucId = $sucId;
                    $new_data->venId = $venta->venId;
                    $new_data->save();

                }

                $this->almacenarExtras($fecha);
                return $this->successResponse($new_data, 200, 'Registro guardado exitosamente');

            }


    }
    public function almacenarExtras($fecha)
    {
        $sucursales = Sucursales::leftJoin('empresas as emp', 'sucursales.empId', '=', 'emp.empId')
            ->leftJoin('ingresos as ing', 'sucursales.sucId', '=', 'ing.sucId')
            ->leftJoin('ventas as ven', function ($join) use ($fecha) {
                $join->on('ing.venId', '=', 'ven.venId')
                    ->where('ven.venFecha', '=', $fecha);
            })
            ->leftJoin('parametros as param', 'ing.tipoId', '=', 'param.id') // Relacionar con parámetros
            ->select(
                'sucursales.sucId',
                'sucursales.sucNombre as sucursal_nombre',
                'emp.empId',
                'emp.empNombre as empresa_nombre',
                'ven.venId',
                'ven.venFecha',
                'ing.tipoId',
                'ing.ingCantidad',
                'param.nombre as tipo_nombre',
                'param.valor'
            )
            ->get()
            ->groupBy('sucId') // Agrupar por sucursal
            ->map(function ($items, $sucId) {
                $sucursal = $items->first(); // Obtener los datos generales de la sucursal
                $ventas = array_values($items->filter(function ($venta) {
                    return !is_null($venta->venId); // Excluir registros donde venId sea nulo
                })->map(function ($venta) {
                    $total_ingesta = $venta->valor * $venta->ingCantidad; // Calcular total por venta
                    return [
                        'venId' => $venta->venId,
                        'tipoId' => $venta->tipoId,
                        'ingCantidad' => $venta->ingCantidad,
                        'nombre' => $venta->tipo_nombre,
                        'fecha' => $venta->venFecha,
                        'valor' => $venta->valor,
                        'total_ingesta' => $total_ingesta,
                    ];
                })->toArray());

                // Calcular total de venta diaria
                $total_venta_diaria = collect($ventas)->sum('total_ingesta');
                $ventas = collect($ventas);
                $ventas= $ventas[0]['venId'];
                $venId= $ventas;
                            return [
                    'total_venta_diaria' => $total_venta_diaria,
                    'venId' => $venId,

                ];
            })
            ->values(); // Reindexar los resultados principales

        // Calcular suma global de todas las sucursales
        $suma_global = $sucursales->sum('total_venta_diaria');
        $ventas = $sucursales;

        $cqlUpdate = Ventas::find($ventas[0]['venId']);
        $cqlUpdate->venManoObra = $suma_global * 0.25;
        $cqlUpdate->venMateriaPrima =  $suma_global * 0.5;
        $cqlUpdate->venEmpaques =  $suma_global * 0.20;
        $cqlUpdate->venCosto =$cqlUpdate->venMateriaPrima + $cqlUpdate->venEmpaques + $cqlUpdate->venManoObra;;
        $cqlUpdate->save();

        return response()->json([
            '$cqlUpdate' => $cqlUpdate, // Agregar total global
        ]);
    }

    public function editarRegistro(Request $request)
    {
        $costo = ( $request->venManoObra +  $request->venMateriaPrima +  $request->venEmpaques );

        $update_data = Ventas::findOrFail($request->venId);
        $update_data->venManoObra = $request->venManoObra;
        $update_data->venMateriaPrima = $request->venMateriaPrima;
        $update_data->venEmpaques = $request->venEmpaques;
        $update_data->venObservacion = $request->venObservacion;
        $update_data->venCosto =$costo;
        $update_data->update();


        if (!$update_data) return $this->errorResponse($update_data, 404, 'Error');
        return $this->successResponse($update_data, 200, 'Registro actualizado exitosamente');
    }

    public function eliminarRegistro($id)
    {
        $delete_data = User::findOrFail($id);
        $delete_data->estado = 0;
        $delete_data->update();

        if (!$delete_data) return $this->errorResponse(500);
        return $this->successResponse(200);
    }

}
