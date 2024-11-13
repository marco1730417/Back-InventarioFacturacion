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


    public function obtenerRegistros()
    {
        $info = Sucursales:: leftJoin('empresas as emp', 'sucursales.empId', '=', 'emp.empId')
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
