<?php

namespace App\Http\Controllers;

use App\Models\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Validators;

class PasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtenemos todos los registros de Password
        $passwords = Password::all()->map(function ($password) {
            // Enmascaramos la contraseña para cada registro
            $password->password = str_repeat('*', 8); // Puedes ajustar el número de asteriscos según prefieras
            return $password;
        });

        return response()->json($passwords);
    }

    public function showPassword($id)
    {
        // Verificar permisos y autorización del usuario
        $passwordRecord = Password::findOrFail($id);

        return response()->json([
            'password' => Crypt::decryptString($passwordRecord->password)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @payload {
     *      site: string,
     *      user_name: string,
     *      password: string,
     *      notes: string nullable,
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string|max:1000'
        ], [
            'site.required' => 'El campo "sitio" es obligatorio.',
            'site.string' => 'El campo "sitio" debe ser una cadena de texto.',
            'site.max' => 'El campo "sitio" no debe exceder los 255 caracteres.',

            'user_name.required' => 'El campo "nombre de usuario" es obligatorio.',
            'user_name.string' => 'El campo "nombre de usuario" debe ser una cadena de texto.',
            'user_name.max' => 'El campo "nombre de usuario" no debe exceder los 255 caracteres.',

            'password.required' => 'El campo "contraseña" es obligatorio.',
            'password.string' => 'El campo "contraseña" debe ser una cadena de texto.',
            'password.min' => 'El campo "contraseña" debe tener al menos 8 caracteres.',

            'notes.string' => 'El campo "notas" debe ser una cadena de texto.',
            'notes.max' => 'El campo "notas" no debe exceder los 1000 caracteres.',
        ]);

        if($validator->fails()){
            return response()->json(['resp' => $validator->errors()], 422);
        }

        $encrypted = Crypt::encryptString($request->password);

        Password::create([
            'site' => $request->site,
            'user_name' => $request->user_name,
            'password' => $encrypted,
            'notes' => $request->notes
        ]);

        return response()->json(['resp' => 'Contrasena creada correctamente']);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Password  $password
     * @return \Illuminate\Http\Response
     */
    public function show(Password $password)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Password  $password
     * @return \Illuminate\Http\Response
     */
    public function edit(Password $password)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Password  $password
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Password $password)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Password  $password
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $password = Password::find($id);
        if(!$password){
            return response()->json(['resp' => 'Password not found'], 404);
        }

        $password->delete();

        return response()->json(['resp' => 'Password destroyed succefully']);
    }
}
