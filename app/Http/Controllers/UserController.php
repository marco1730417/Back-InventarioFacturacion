<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{

     /**
     * Obtener el objeto User como json
     */
    public function getUsers()
    {
        return response(User::all());
    }
}
