<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use Validator;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function getClients() 
    {
        $cliente_info = Cliente::all();
       // return $cliente_info;
    //    return Response::json($cliente_info);
     //   return Response::json($cliente_info, 200);
        return response()->json($cliente_info);
    }

}
