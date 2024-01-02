<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class UsuariosController extends ApiResponseController
{
    public function obtenerRegistros()
    {
        $info = User::where('estado', 1)->get();
        return $info;
    }

    public function guardarRegistro(Request $request)
    {

        $existente = User::where('identificacion', $request->identificacion)
            ->whereNull('deleted_at')
            ->where('estado', 1)
            ->count();

        if ($existente > 0) return $this->errorResponse($existente, 404, 'Registro existente');


        $new_data = new User;
        $new_data->name = strtoupper($request->name);
        $new_data->email = $request->email;
        $new_data->identificacion =  $request->identificacion;
        $new_data->direccion =strtoupper($request->direccion);
        $new_data->cargo = $request->cargo;
        $new_data->sueldo = $request->sueldo;
        $new_data->password = bcrypt($request->identificacion);
        $new_data->perfil = 2;
        $new_data->estado = 1;

        $new_data->save();

        $respuesta = [
            'data' => $new_data
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
    }
    public function editarRegistro(Request $request)
    {


        $update_data = User::findOrFail($request->id);
        $update_data->name = strtoupper($request->name);
        $update_data->email = $request->email;
        $update_data->identificacion = $request->identificacion;
        $update_data->direccion = strtoupper($request->direccion);
        $update_data->cargo = $request->cargo;
        $update_data->sueldo = $request->sueldo;
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
