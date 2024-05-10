<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InstructorController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'middle_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'company_email' => 'required|email|unique:users,company_email',
            'phone' => 'required|string',
            'cellphone' => 'required|string',
            'base' => 'required|exists:bases,id',
            'curp' => 'required|string|unique:users,curp',
        ],
        [
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre no es válido',
            'last_name.required' => 'El apellido es requerido',
            'last_name.string' => 'El apellido no es válido',
            'middle_name.required' => 'El segundo apellido es requerido',
            'middle_name.string' => 'El segundo apellido no es válido',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico no es válido',
            'email.unique' => 'El correo electrónico ya está en uso',
            'company_email.required' => 'El correo electrónico de la empresa es requerido',
            'company_email.unique' => 'El correo electrónico de la empresa ya está en uso',
            'company_email.email' => 'El correo electrónico de la empresa no es válido',
            'phone.required' => 'El teléfono es requerido',
            'phone.string' => 'El teléfono no es válido',
            'cellphone.required' => 'El celular es requerido',
            'cellphone.string' => 'El celular no es válido',
            'base.required' => 'La base es requerida',
            'base.exists' => 'La base no existe',
            'curp.required' => 'La CURP es requerida',
            'curp.unique' => 'La CURP ya está en uso',
            'curp.string' => 'La CURP no es válida',
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $instructor = new User();
        $instructor->name = $request->name;
        $instructor->middle_name = $request->middle_name;
        $instructor->last_name = $request->last_name;
        $instructor->email = $request->email;
        $instructor->company_email = $request->company_email;
        $instructor->phone = $request->phone;
        $instructor->cellphone = $request->cellphone;
        $instructor->curp = $request->curp;
        $instructor->user_type = 'instructor';
        $instructor->id_base = $request->base;
        $instructor->password = bcrypt($instructor->curp);
        $instructor->save();

        if($instructor->id_base == 1){
            $instructor->user_identification = 'ET' . $instructor->id;
        } else if($instructor->id_base == 2){
            $instructor->user_identification = 'EQ' . $instructor->id;
        }
        $instructor->save();

        return response()->json($instructor->user_identification, 201);
    }
}
