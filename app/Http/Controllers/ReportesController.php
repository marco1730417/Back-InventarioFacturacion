<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use App\Models\Marcaciones;
use App\Models\Agendamiento;
use Illuminate\Support\Facades\DB;
use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class ReportesController extends ApiResponseController
{
    public function calcularMarcaciones(Request $request)
    {

        $mes = $request->mes['code'];
        $anio =  $request->anio['code'];
        $usuario_id = $request->usuario_id['id'];

        $info = Marcaciones::select(

            DB::raw('(SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . ' ) AS horario_establecido'),

            DB::raw('TIMEDIFF(hora_salida, hora_entrada) AS tiempo_trabajo'),


          /*  DB::raw('TIMEDIFF(TIMEDIFF(hora_salida, hora_entrada), (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . ')) AS tiempo_excedente'),*/



            DB::raw('CASE
            WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1 THEN
                TIMEDIFF(
                    TIMEDIFF(hora_salida, hora_entrada),
                    (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . ')
                )
            ELSE
                TIMEDIFF(hora_salida, hora_entrada)
        END AS tiempo_excedente'),





            DB::raw('CASE
            WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
            THEN

           ( TIME_TO_SEC( TIMEDIFF(TIMEDIFF(hora_salida, hora_entrada), (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . '))) /3600) * (SELECT valor FROM parametros where nombre = "HoraOrdinaria")

    ELSE

    (TIME_TO_SEC(TIMEDIFF(hora_salida, hora_entrada)) / 3600) *
            (SELECT valor FROM parametros WHERE nombre = "HoraExtraordinaria")

        END AS valor_pagar'),

            DB::raw('CASE
        WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
        THEN

        "Ord"

            ELSE

            "Ext"

    END AS tipo_dia'),

            DB::raw('CASE
    WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
    THEN

    (SELECT valor FROM parametros where nombre = "HoraOrdinaria")

        ELSE

        (SELECT valor FROM parametros where nombre = "HoraExtraordinaria")

END AS valor_hora'),

            'marcaciones.*'
        )

            ->where('estado', 1)
            ->where('usuario_id', $usuario_id)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->get();

        return $info;
    }
    public function calcularMarcacionesV1(Request $request)
    {

        $mes = $request->mes['code'];
        $anio =  $request->anio['code'];
        $usuario_id = $request->usuario_id['id'];

        $info = Marcaciones::select(

            DB::raw('(SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . ' ) AS horario_establecido'),

            DB::raw('TIMEDIFF(hora_salida, hora_entrada) AS tiempo_trabajo'),


            DB::raw('TIMEDIFF(TIMEDIFF(hora_salida, hora_entrada), (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . ')) AS tiempo_excedente'),


            DB::raw('CASE
            WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
            THEN

           ( TIME_TO_SEC( TIMEDIFF(TIMEDIFF(hora_salida, hora_entrada), (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . '))) /3600) * (SELECT valor FROM parametros where nombre = "HoraOrdinaria")

                ELSE
                ( TIME_TO_SEC( TIMEDIFF(TIMEDIFF(hora_salida, hora_entrada), (SELECT numero_horas_laborables FROM users WHERE id = ' . $usuario_id . '))) /3600) * (SELECT valor FROM parametros where nombre = "HoraExtraordinaria")

        END AS valor_pagar'),

        DB::raw('CASE
        WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
        THEN

        "Ord"

            ELSE

            "Ext"

    END AS tipo_dia'),

    DB::raw('CASE
    WHEN (SELECT estado FROM agendamientos WHERE marcaciones.fecha = agendamientos.fecha) = 1
    THEN

    (SELECT valor FROM parametros where nombre = "HoraOrdinaria")

        ELSE

        (SELECT valor FROM parametros where nombre = "HoraExtraordinaria")

END AS valor_hora'),

        'marcaciones.*'
        )

              ->where('estado', 1)
            ->where('usuario_id', $usuario_id)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->get();

        return $info;
    }

}
