<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Marcaciones;

use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class MarcacionesController extends ApiResponseController
{
    public function obtenerRegistrosMarcaciones(Request $request)
    {

        $info = Marcaciones::where('estado', 1)
        ->where('usuario_id',$request->usuario_id)
        ->get();

        return $info;
    }

    public function guardarRegistroEntrada(Request $request)
    {

        try {
            //code...
            $existente = Marcaciones::where('usuario_id', $request->usuario_id)
            ->where('estado', 1)
            ->whereNotNull('fecha_hora_entrada')
            ->count();

        if ($existente > 0) return $this->errorResponse($existente, 404, 'Registro existente');

         $date=date("Y-m-d h:i:s");
         

        $new_data = new Marcaciones;
        $new_data->fecha_hora_entrada = $date;
        $new_data->usuario_id = $request->usuario_id;
        $new_data->estado = 1;

        $new_data->save();

        $respuesta = [
            'data' => $new_data
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->errorResponse($existente, 404, $th);

        }

      
    }


}
