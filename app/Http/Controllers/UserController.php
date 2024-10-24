<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    public function test(){

    }

    public function getUserData(){
        $user = Auth::user();
        $userType = $user->user_type;

        if($user->user_type == 'student'){
            $user = Student::where('user_identification', $user->user_identification)->first();
            $userType = 'student';
        }else {
            $user = Employee::where('user_identification', $user->user_identification)->first();
        }
        return response()->json($user, 200);
    }

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

        $expiresAt = now()->addMinutes(30*8);
        $minutesUntilExpiration = now()->diffInMinutes($expiresAt);

        $cookie = cookie('jwt', $token, $minutesUntilExpiration);
        Cache::put($user->user_identification, $user->user_type, $expiresAt);

        $userType = $user->user_type;
        if($user->user_type == 'student'){
            $user = Student::where('user_identification', $user->user_identification)
            ->with('career')
            ->first();
            $userType = 'student';
        }


        $response = response()->json([
            'message' => 'Se ha logeado correctamente',
            'data' => $user,
            'jwt' => $token,
            'expires_at' => $expiresAt,
            'user_type' => $userType
        ])->withCookie($cookie);

        return $response;
    }

    public function logout(){
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        };

        $user->currentAccessToken()->delete();

        $cookie = Cookie::forget('jwt');
        Cache::forget($user->user_identification);

        return response()->json(['message' => 'Se ha cerrado sesión correctamente'])->withCookie($cookie);
    }

    function getEmployes(){
        $user_type = Auth::user()->user_type;
        switch($user_type){
            case 'root':
                $users = User::whereIn('user_type', ['admin', 'employee'])->get(['id', 'name']);
                break;
            case 'admin':
                $users = User::whereIn('user_type', 'employee')->get(['id', 'name']);
                break;
            case 'employee':
                $users = User::find(Auth()->user()->id)->get(['id', 'name']);
                break;
        }
        return response()->json([
            "employes" => $users
        ]);
    }

    public function getClientId($clientId){
        $userIdentification = User::select('user_identification')->where('id', $clientId)->first();
        try{
            $employeId = Employee::select('id')->where('user_identification', $userIdentification->user_identification)->first()->id;
        }catch(\Exception $e){
            $employeId = Student::select('id')->where('user_identification', $userIdentification->user_identification)->first()->id;
        }
        return $employeId;
    }

    function getBaseAuth(User $user){
        $user_identification = $user->user_identification;
        $base = User::where('user_identification', $user_identification)->first()->base;
        return $base;
    }


    function getIdEmploye(string $user_identification){
        $employeId = Employee::select('id')->where('user_identification', $user_identification)->first()->id;
        return $employeId;
    }

}
