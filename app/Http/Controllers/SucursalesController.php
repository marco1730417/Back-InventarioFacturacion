<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Sucursales;

use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SucursalesController extends ApiResponseController
{
    public function obtenerRegistros($fecha)
    {
        // Consulta base
        $registros = Sucursales::leftJoin('empresas as emp', 'sucursales.empId', '=', 'emp.empId')
            ->leftJoin('ingestas_empresa as ing', 'emp.empId', '=', 'ing.empId')  // Relación empresa -> tipos de ingestas

            ->leftJoin('ventas as v', function ($join) use ($fecha) {
                $join->on('sucursales.sucId', '=', 'v.sucId')
                    ->whereDate('v.venFecha', $fecha); // Filtrar por fecha
            })

            ->leftJoin('ingresos as i', function ($join) {
                $join->on('v.venId', '=', 'i.venId'); // Relación venta -> ingresos
            })

            ->select(
                'sucursales.sucId',
                'sucursales.sucNombre as sucursal_nombre',
                'emp.empId',
                'emp.empNombre as empresa_nombre',
                'v.venId',
                'v.venFecha',
                'i.tipoId',
                'i.ingCantidad as ingresos_cantidad'
            )
            ->orderBy('sucursales.empId', 'asc')
            ->get();

        // Estructura de resultados agrupados
        $ventasAgrupadas = [];

        foreach ($registros as $registro) {
            $ventaId = $registro->venId;

            // Si la venta ya existe en el array agrupado, añadimos el ingreso al sub-array 'ingresos'
            if (isset($ventasAgrupadas[$ventaId])) {
                $ventasAgrupadas[$ventaId]['ingresos'][] = [
                    'tipoId' => $registro->tipoId,
                    'ingresos_cantidad' => $registro->ingresos_cantidad
                ];
            } else {
                // Nueva entrada de venta con sus datos y un sub-array para 'ingresos'
                $ventasAgrupadas[$ventaId] = [
                    'sucId' => $registro->sucId,
                    'sucursal_nombre' => $registro->sucursal_nombre,
                    'empId' => $registro->empId,
                    'empresa_nombre' => $registro->empresa_nombre,
                    'venFecha' => $registro->venFecha,
                    'ingresos' => [
                        [
                            'tipoId' => $registro->tipoId,
                            'ingresos_cantidad' => $registro->ingresos_cantidad
                        ]
                    ]
                ];
            }
        }

        // Convertimos a array para obtener un índice secuencial
        return array_values($ventasAgrupadas);
    }


    public function obtenerRegistros1($fecha)
    {
        $info = Sucursales::leftJoin('empresas as emp', 'sucursales.empId', '=', 'emp.empId')
            ->leftJoin('ingestas_empresa as ing', 'emp.empId', '=', 'ing.empId')  // Relación empresa -> tipos de ingestas

            ->leftJoin('ventas as v', function ($join) use ($fecha) {
                $join->on('sucursales.sucId', '=', 'v.sucId')
                    //->on('ing.tipoId', '=', 'v.tipoId') // Relación sucursal -> tipo de ingesta -> ventas
                    ->whereDate('v.venFecha', $fecha); // Filtrar por fecha
            })

            ->leftJoin('ingresos as i', function ($join) use ($fecha) {
                $join->on('v.venId', '=', 'i.venId');
                  //  ->on('ing.tipoId', '=', 'i.tipoId'); // Relación sucursal -> tipo de ingesta -> ingresos
                   // ->whereDate('i.fecha', $fecha); // Filtrar por fecha
            })
            ->select(
                'sucursales.sucId',
                'sucursales.sucNombre as sucursal_nombre',
                'emp.empId',
                'emp.empNombre as empresa_nombre',
                'i.tipoId',
                'i.ingCantidad as ingresos_cantidad', // cantidad de ingresos
                'v.venFecha'
            )
            ->orderBy('sucursales.empId', 'asc')
            ->get();

        return $info;
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
