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
                'param.nombre as tipo_nombre'
            )
            ->get()
            ->groupBy('sucId') // Agrupar por sucursal
            ->map(function ($items, $sucId) {
                $sucursal = $items->first(); // Obtener los datos generales de la sucursal
                return [
                    'sucId' => $sucursal->sucId,
                    'sucursal_nombre' => $sucursal->sucursal_nombre,
                    'empId' => $sucursal->empId,
                    'empresa_nombre' => $sucursal->empresa_nombre,
                    'ventas' => $items->map(function ($venta) {
                        return [
                            'venId' => $venta->venId,
                            'tipoId' => $venta->tipoId,
                            'ingCantidad' => $venta->ingCantidad,
                            'nombre' => $venta->tipo_nombre,
                            'fecha' => $venta->venFecha,
                        ];
                    })->toArray()
                ];
            })
            ->values(); // Reindexar los resultados

        return response()->json($sucursales);
    }


    public function obtenerRegistros1($fecha)
    {

        $sucursales = Sucursales::leftJoin('empresas as emp', 'sucursales.empId', '=', 'emp.empId')
                ->select(
                'sucursales.sucId',
                'sucursales.sucNombre as sucursal_nombre',
                'emp.empId',
                'emp.empNombre as empresa_nombre'
            )
            ->get();

return $sucursales;
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

       /*  $respuesta = [
            'data' => $new_data
        ]; */
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
