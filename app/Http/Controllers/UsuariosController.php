<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use Carbon\Carbon;
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


// Convertir horas a objetos Carbon
        $horaEntrada = Carbon::createFromFormat('H:i', $request->hora_entrada);
        $horaSalida = Carbon::createFromFormat('H:i', $request->hora_salida);

// Calcular la diferencia en minutos
        $minutosTrabajados = $horaEntrada->diffInMinutes($horaSalida);
        // Convertir minutos a formato HH:MM:SS
        $horas = intdiv($minutosTrabajados, 60); // División entera para obtener horas
        $minutos = $minutosTrabajados % 60;     // El resto son los minutos
        $segundos = 0;                          // Siempre 0 para este caso

// Crear formato HH:MM:SS
        $formatoTiempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);


        $new_data = new User;
        $new_data->name = strtoupper($request->name);
        $new_data->email = $request->email;
        $new_data->identificacion =  $request->identificacion;
        $new_data->direccion =strtoupper($request->direccion);
        $new_data->cargo = $request->cargo;
        $new_data->sueldo = $request->sueldo;
        $new_data->password = bcrypt($request->identificacion);
        $new_data->perfil = 2;
        $new_data->numero_horas_laborables = $formatoTiempo;
        $new_data->hora_entrada = $request->hora_entrada;
        $new_data->hora_salida = $request->hora_salida;

        $new_data->estado = 1;

        $new_data->save();

        $respuesta = [
            'data' => $new_data
        ];
        return $this->successResponse($respuesta, 200, 'Registro guardado exitosamente');
    }
    public function editarRegistro(Request $request)
    {

// Convertir horas a objetos Carbon
        // Validar que las horas están presentes y tienen el formato correcto
        try {

            $horaEntrada = Carbon::createFromFormat('H:i', $request->hora_entrada);
            $horaSalida = Carbon::createFromFormat('H:i', $request->hora_salida);
        }
        catch (\Exception $e) {
        return $this->errorResponse(null, 400, 'El formato de las horas es inválido.');
    }
// Calcular la diferencia en minutos
        $minutosTrabajados = $horaEntrada->diffInMinutes($horaSalida);
        // Convertir minutos a formato HH:MM:SS
        $horas = intdiv($minutosTrabajados, 60); // División entera para obtener horas
        $minutos = $minutosTrabajados % 60;     // El resto son los minutos
        $segundos = 0;                          // Siempre 0 para este caso

// Crear formato HH:MM:SS
        $formatoTiempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);


        $update_data = User::findOrFail($request->id);
        $update_data->name = strtoupper($request->name);
        $update_data->email = $request->email;
        $update_data->identificacion = $request->identificacion;
        $update_data->direccion = strtoupper($request->direccion);
        $update_data->cargo = $request->cargo;
        $update_data->sueldo = $request->sueldo;
        $update_data->numero_horas_laborables =$formatoTiempo;
        $update_data->hora_entrada = $request->hora_entrada;
        $update_data->hora_salida = $request->hora_salida;
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
