<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Sucursales;

use Auth;
use Validator;
use DateTime;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SucursalesController extends ApiResponseController
{
    public function obtenerRegistros($fecha)
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

            return [
                'sucId' => $sucursal->sucId,
                'sucursal_nombre' => $sucursal->sucursal_nombre,
                'empId' => $sucursal->empId,
                'empresa_nombre' => $sucursal->empresa_nombre,
                'ventas' => $ventas,
                'total_venta_diaria' => $total_venta_diaria, // Agregar total de la sucursal
            ];
        })
        ->values(); // Reindexar los resultados principales

    // Calcular suma global de todas las sucursales
    $suma_global = $sucursales->sum('total_venta_diaria');

    return response()->json([
        'sucursales' => $sucursales,
        'total_venta_general' => $suma_global, // Agregar total global
    ]);
}


    public function guardarRegistro(Request $request)
    {

        $new_data = new Sucursales;
        $new_data->empId = $request->empId;
        $new_data->sucNombre = strtoupper($request->sucNombre);
        $new_data->sucDescripcion = strtoupper($request->sucDescripcion);
        $new_data->sucUbicacion = strtoupper($request->sucUbicacion);
        $new_data->sucCorreo = $request->sucCorreo;
        $new_data->sucEstado =  1;
        $new_data->sucTelefono =$request->sucTelefono;
        $new_data->sucObservacion =$request->sucObservacion;
        $new_data->save();


        return $this->successResponse($new_data, 200, 'Registro guardado exitosamente');
    }
    public function editarRegistro(Request $request)
    {
        $update_data = Sucursales::findOrFail($request->sucId);
        $update_data->sucNombre = strtoupper($request->sucNombre);
        $update_data->sucDescripcion = strtoupper($request->sucDescripcion);
        $update_data->sucUbicacion = strtoupper($request->sucUbicacion);
        $update_data->sucCorreo = $request->sucCorreo;
        $update_data->sucTelefono =$request->sucTelefono;
        $update_data->sucObservacion =$request->sucObservacion;

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
