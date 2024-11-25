<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\Parametros;
use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ParametrosController extends ApiResponseController
{
    public function obtenerRegistros()
    {
        $cqlQuery = Parametros::all();
        return $cqlQuery;
    }

    public function guardarRegistro(Request $request)
    {


        $new_data = new Parametros;
        $new_data->nombre = strtoupper($request->nombre);
        $new_data->descripcion = $request->descripcion;
        $new_data->valor =  $request->valor;
        $new_data->save();

        $respuesta = [
            'data' => $new_data
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
    }
    public function editarRegistro(Request $request)
    {
        $update_data = Parametros::findOrFail($request->id);
        $update_data->nombre = strtoupper($request->nombre);
        $update_data->descripcion = $request->descripcion;
        $update_data->valor = $request->valor;
        $update_data->update();


        if (!$update_data) return $this->errorResponse($update_data, 404, 'Error');
        return $this->successResponse($update_data, 200, 'Registro actualizado exitosamente');
    }

    public function eliminarRegistro($id)
    {
        $delete_data = Parametros::findOrFail($id);
        $delete_data->delete();

        if (!$delete_data) return $this->errorResponse(500);
        return $this->successResponse(200);
    }

}
