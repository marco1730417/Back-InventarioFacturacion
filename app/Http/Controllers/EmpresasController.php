<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Empresas;

use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class EmpresasController extends ApiResponseController
{
    public function obtenerRegistros()
    {
        $info = Empresas::all();
        return $info;
    }

    public function guardarRegistro(Request $request)
    {
       
        $new_data = new Empresas;
        $new_data->empNombre = strtoupper($request->empNombre);
        $new_data->empDescripcion = strtoupper($request->empDescripcion);
        $new_data->empUbicacion = strtoupper($request->empUbicacion);
        $new_data->empCorreo = $request->empCorreo;
        $new_data->empEstado =  1;
        $new_data->empTelefono =$request->empTelefono;
        $new_data->empObservaciones =$request->empObservaciones;
        $new_data->save();

        $respuesta = [
            'data' => $new_data
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
    }
    public function editarRegistro(Request $request)
    {


        $update_data = Empresas::findOrFail($request->empId);
        $update_data->empNombre = strtoupper($request->empNombre);
        $update_data->empDescripcion = strtoupper($request->empDescripcion);
        $update_data->empUbicacion = strtoupper($request->empUbicacion);
        $update_data->empCorreo = $request->empCorreo;
        $update_data->empTelefono =$request->empTelefono;
        $update_data->empObservaciones =$request->empObservaciones;
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
    public function resetearClave($id)
    {
        $update_data = User::findOrFail($id);
        $update_data->password = bcrypt($update_data->identificacion);
        $update_data->update();

        if (!$update_data) return $this->errorResponse(500);
        return $this->successResponse(200);
    }
}
