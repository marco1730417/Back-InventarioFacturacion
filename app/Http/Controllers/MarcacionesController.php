<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Marcaciones;
use App\Models\Agendamiento;

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
            ->where('usuario_id', $request->usuario_id)
            ->get();

        return $info;
    }

    public function guardarRegistroEntrada(Request $request)
    {

        try {
            //code...
            $existente = Marcaciones::where('usuario_id', $request->usuario_id)
                ->where('estado', 1)
                ->whereNotNull('hora_entrada')
                ->count();

            if ($existente > 0) return $this->errorResponse($existente, 404, 'Registro existente');

            $fecha = date("Y-m-d");
            $hora_entrada = date("H:i:s");

            $new_data = new Marcaciones;
            $new_data->hora_entrada = $hora_entrada;
            $new_data->fecha = $fecha;
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

    public function guardarRegistroSalida(Request $request)
    {

        try {
            //code...
            $existente = Marcaciones::where('usuario_id', $request->usuario_id)
                ->where('estado', 1)
                ->whereNotNull('hora_salida')
                ->count();

            if ($existente > 0) return $this->errorResponse($existente, 404, 'Registro existente');

            $fecha = date("Y-m-d");
            $hora_entrada_existente = Marcaciones::where('usuario_id', $request->usuario_id)
                ->where('estado', 1)
                ->whereNotNull('hora_entrada')
                ->where('fecha', $fecha)
                ->first();

            if ($hora_entrada_existente) {

                Marcaciones::where('id', $hora_entrada_existente->id)->update([

                    'hora_salida' => date("H:i:s")

                ]);
            }

            $respuesta = [
                'data' => $hora_entrada_existente
            ];
            return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
        } catch (\Throwable $th) {
            //throw $th;
            return  $this->errorResponse($existente, 404, $th);
        }
    }



    public function cargarAgendamiento(Request $request)
    {


        $fecha = date('Y-m-d H:i:s');

        $req_mes = $request->mes['code'];

        $req_anio =  $request->anio['code'];


        $validar_fechas_creadas = Agendamiento::where('anio', $req_anio)->where('mes', $req_mes)->count();

        //Creacion => En caso que no existan fechas dinamicas generadas
        if ($validar_fechas_creadas == 0) {

            $fecha_inicio = date($req_anio . '-' . $req_mes . '-01');

            $fecha_fin = date($req_anio . '-' . $req_mes . '-t');

            $rango = $this->crearRangoFechas($fecha_inicio, $fecha_fin, 'Y-m-d');

            $range_curado = [];

            //Metodo que permite afinar las fechas sobrepasadas, especialmente febrero 
            for ($i = 0, $size = count($rango); $i < $size; ++$i) {

                $fechaSeparada = explode("-", $rango[$i]);

                $anyo = $fechaSeparada[1];  // numero del mes Ej : 01 02 03

                if ($anyo == $req_mes) {

                    $range_curado[$i] = $rango[$i]; // array formado solo con los dias que corresponden al mes
                    $cqlDisponibilidad = new Agendamiento();
                    $cqlDisponibilidad->anio = $req_anio;
                    $cqlDisponibilidad->mes = $req_mes;
                    $cqlDisponibilidad->estado = 1;
                    $cqlDisponibilidad->dia = $this->get_nombre_dia($rango[$i]);
                    $cqlDisponibilidad->fecha = $rango[$i];
                    $cqlDisponibilidad->save();
                }
            }
        }
        $respuesta = [
            'data' => Agendamiento::where('anio', $req_anio)->where('mes', $req_mes)->get()
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
        /* 
        $array_response['status'] = 200;

        $array_response['message'] = 'Registro ingresado exitosamente';

        return response()->json($array_response, 200);
     */
    }

    public function obtenerRegistrosAgendamiento(Request $request)
    {

        $req_mes = $request->mes['code'];

        $req_anio =  $request->anio['code'];

        $info = Agendamiento::where('anio', $req_anio)->where('mes', $req_mes)->get();

        return $info;
    }

    protected function get_nombre_dia($fecha)
    {
        $fechats = strtotime($fecha); //pasamos a timestamp

        //el parametro w en la funcion date indica que queremos el dia de la semana
        //lo devuelve en numero 0 domingo, 1 lunes,....
        switch (date('w', $fechats)) {
            case 0:
                return "Domingo";
                break;
            case 1:
                return "Lunes";
                break;
            case 2:
                return "Martes";
                break;
            case 3:
                return "Miercoles";
                break;
            case 4:
                return "Jueves";
                break;
            case 5:
                return "Viernes";
                break;
            case 6:
                return "Sabado";
                break;
        }
    }

    protected function crearRangoFechas($inicio, $fin)
    {
        $start = $inicio;

        $end = $fin;

        $range = array();

        if (is_string($start) === true) $start = strtotime($start);

        if (is_string($end) === true) $end = strtotime($end);

        if ($start > $end) return $this->crearRangoFechas($end, $start);

        do {

            $range[] = date('Y-m-d', $start);

            $start = strtotime("+ 1 day", $start);
        } while ($start <= $end);
        return $range;
    }
    public function cambiarEstadoAgendamiento($id)
    {
        $update_data_info = Agendamiento::findOrFail($id);

        if ($update_data_info->estado == 1) {
            $update_data = Agendamiento::findOrFail($id);

            $update_data->estado = 2;
            $update_data->update();
        }

        if ($update_data_info->estado == 2) {
            $update_data = Agendamiento::findOrFail($id);

            $update_data->estado = 1;
            $update_data->update();
        }


        if (!$update_data) return $this->errorResponse(500);
        return $this->successResponse($update_data);
    }
}
