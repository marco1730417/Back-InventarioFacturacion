<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarcacionesController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ParametrosController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 */

Route::prefix('/')->group( function () {

    Route::post('login',[LoginController::class, 'login']);
    Route::post('register', [LoginController::class, 'register']);

    Route::middleware('auth:api')->get('/get-users', 'App\Http\Controllers\UserController@getUsers');
   // Route::middleware('auth:api')->get('/get-clientes', [ClientesController::class, 'getClients']);
    Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']);


});


Route::middleware('auth:api')->prefix('clientes')->group(function () {
  Route::get('obtener-registros', [ClientesController::class, 'obtenerRegistros']);
  Route::post('guardar-registro', [ClientesController::class, 'guardarRegistro']);
  Route::post('editar-registro', [ClientesController::class, 'editarRegistro']);
  Route::get('eliminar-registro/{id} ', [ClientesController::class, 'eliminarRegistro']);
});

Route::middleware('auth:api')->prefix('usuarios')->group(function () {
  Route::get('obtener-registros', [UsuariosController::class, 'obtenerRegistros']);
  Route::post('guardar-registro', [UsuariosController::class, 'guardarRegistro']);
  Route::post('editar-registro', [UsuariosController::class, 'editarRegistro']);
  Route::get('eliminar-registro/{id} ', [UsuariosController::class, 'eliminarRegistro']);
  Route::get('resetear-clave/{id} ', [UsuariosController::class, 'resetearClave']);

});

Route::middleware('auth:api')->prefix('marcaciones')->group(function () {
  Route::post('obtener-registro-marcaciones', [MarcacionesController::class, 'obtenerRegistrosMarcaciones']);
  Route::post('guardar-registro-entrada', [MarcacionesController::class, 'guardarRegistroEntrada']);
  Route::post('guardar-registro-salida', [MarcacionesController::class, 'guardarRegistroSalida']);
  Route::post('guardar-marcacion', [MarcacionesController::class, 'guardarMarcacion']);
  Route::post('guardar-marcacion-salida', [MarcacionesController::class, 'guardarMarcacionSalida']);

});

Route::middleware('auth:api')->prefix('agendamiento')->group(function () {
  Route::post('cargar-agendamiento', [MarcacionesController::class, 'cargarAgendamiento']);
  Route::post('obtener-registro-agendamiento', [MarcacionesController::class, 'obtenerRegistrosAgendamiento']);
  Route::get('cambiar-estado-agendamiento/{id}', [MarcacionesController::class, 'cambiarEstadoAgendamiento']);

});
Route::middleware('auth:api')->prefix('reportes')->group(function () {
  Route::post('calcular-marcaciones', [ReportesController::class, 'calcularMarcaciones']);
  Route::post('obtener-registro-agendamiento', [ReportesController::class, 'obtenerRegistrosAgendamiento']);
  Route::get('cambiar-estado-agendamiento/{id}', [ReportesController::class, 'cambiarEstadoAgendamiento']);

});
Route::middleware('auth:api')->prefix('empresas')->group(function () {
  Route::get('obtener-registros', [EmpresasController::class, 'obtenerRegistros']);
  Route::post('guardar-registro', [EmpresasController::class, 'guardarRegistro']);
  Route::post('editar-registro', [EmpresasController::class, 'editarRegistro']);
  Route::get('obtener-tipo-ingestas', [EmpresasController::class, 'obtenerTipoIngestas']);

  Route::post('guardar-marcacion', [EmpresasController::class, 'guardarMarcacion']);
  Route::post('guardar-marcacion-salida', [EmpresasController::class, 'guardarMarcacionSalida']);

});

Route::middleware('auth:api')->prefix('sucursales')->group(function () {
  Route::get('obtener-registros/{fecha}', [SucursalesController::class, 'obtenerRegistros']);
  Route::post('guardar-registro', [SucursalesController::class, 'guardarRegistro']);
  Route::post('editar-registro', [SucursalesController::class, 'editarRegistro']);

  Route::post('guardar-marcacion', [SucursalesController::class, 'guardarMarcacion']);
  Route::post('guardar-marcacion-salida', [SucursalesController::class, 'guardarMarcacionSalida']);

});

Route::middleware('auth:api')->prefix('ventas')->group(function () {
    Route::get('obtener-registros/{fecha}', [VentasController::class, 'obtenerRegistros']);
    Route::get('obtener-ingestas-empresa/{empId}', [VentasController::class, 'ingestasEmpresa']);

    Route::post('guardar-registro', [VentasController::class, 'guardarRegistro']);

    Route::post('editar-registro', [VentasController::class, 'editarRegistro']);

    Route::post('guardar-detalle-venta', [VentasController::class, 'guardarDetalleVenta']);


    Route::post('guardar-marcacion', [VentasController::class, 'guardarMarcacion']);
    Route::post('guardar-marcacion-salida', [VentasController::class, 'guardarMarcacionSalida']);

});

Route::middleware('auth:api')->prefix('parametros')->group(function () {
    Route::get('obtener-registros', [ParametrosController::class, 'obtenerRegistros']);
    Route::post('guardar-registro', [ParametrosController::class, 'guardarRegistro']);
    Route::post('editar-registro', [ParametrosController::class, 'editarRegistro']);
    Route::get('eliminar-registro/{id} ', [ParametrosController::class, 'eliminarRegistro']);

});
