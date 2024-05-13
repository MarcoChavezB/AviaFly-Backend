<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'user_identification' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $user = User::where('user_identification', $request->user_identification)->first();

        if(!$user){
            return response()->json(["errors" => ["Usuario no encontrado"]], 404);
        }

        if(!$user || !Hash::check($request->password, $user->password)){
            return response()->json([
                'errors' => ['Contraseña incorrecta']
            ], 401);
        }

        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'message' => 'Se ha logeado correctamente',
            'data' => $user,
            'jwt' => $token,
        ]);
    }

    public function logout(){

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        };

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Se ha cerrado sesión correctamente']);

    }
}
