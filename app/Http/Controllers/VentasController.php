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
        // Inicia una transacción
        DB::beginTransaction();
        try {
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

        foreach ($request['cantidades'] as $tipoId => $cantidad) {

            $new_data = new Ingresos;
            $new_data->tipoId = $tipoId;
            $new_data->ingCantidad = $cantidad;
            $new_data->sucId = $sucId;
            $new_data->venId = $venta->venId;
            $new_data->save();

        }
// Confirmar la transacción
            DB::commit();
            return $this->successResponse($new_data, 200, 'Registro guardado exitosamente');
            
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500, 'Ocurrió un error al guardar los datos');
        }

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
