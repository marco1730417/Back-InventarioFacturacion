<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Sucursales;
use App\Models\Ventas;


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
