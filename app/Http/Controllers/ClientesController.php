<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiResponseController;
use App\Models\User;
use Auth;
use Validator;
use App\Models\Cliente;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ClientesController extends ApiResponseController
{
    public function obtenerRegistros() 
    {
        $cliente_info = Cliente::where('estado',1)->get();
        return $cliente_info;
    }

    public function guardarRegistro(Request $request){
        
        $cliente_existente=Cliente::where('ruc',$request->ruc)
        ->whereNull('deleted_at')
        ->count();

   if($cliente_existente>0) return $this->errorResponse($cliente_existente,404,'Registro existente');


        $new_cliente = new Cliente;
        $new_cliente->nombre =$request->nombre;
        $new_cliente->email = $request->email;
        $new_cliente->telefono =  $request->telefono;
        $new_cliente->ruc =  $request->ruc;
        $new_cliente->direccion = $request->direccion;
        $new_cliente->estado = 1;
      
        $new_cliente->save();
       
        $respuesta = [
            'cliente' => $new_cliente
        ];
        return $this->successResponse($respuesta,200,'Registro guardado exitosamente');

    }
    public function editarRegistro(Request $request){
     
      
        $cliente_update = Cliente::findOrFail($request->id);
        $cliente_update->nombre = $request->nombre;
        $cliente_update->email = $request->email;
        $cliente_update->telefono = $request->telefono;
        $cliente_update->direccion = $request->direccion;
        $cliente_update->ruc =$request->ruc;
        $cliente_update->update(); 


        if (!$cliente_update) return $this->errorResponse($cliente_update,404,'Error');
        return $this->successResponse($cliente_update,200,'Registro actualizado exitosamente');

    }

    public function eliminarRegistro($id)
    {        
        $cliente_delete = Cliente::findOrFail($id);
        $cliente_delete->estado =0;
        $cliente_delete->update(); 

        if (!$cliente_delete) return $this->errorResponse(500);
        return $this->successResponse(200);


    }

}
