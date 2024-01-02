<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
 
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            $user = User::where('email',$request->email)->first();
            
            return response()->json(['token' => $token,'user'=>$user], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    } 

   /**
     * Cierre de sesión (anular el token)
     */
    public function logout(Request $request)
    {
        //return $request->id->user();

         $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!', 'status' => 200];
        return response($response, 200);
    }
    
    /**
     * Registration
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
       
        $token = $user->createToken('LaravelAuthApp')->accessToken;
 
        return response()->json(['token' => $token], 200);
    }
     /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
