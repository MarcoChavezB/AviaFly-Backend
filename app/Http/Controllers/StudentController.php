<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'register_date' => 'required|date',
                'name' => 'required|string',
                'last_names' => 'required|string',
                'curp' => 'required|string|unique:users,curp',
                'phone' => 'required|string',
                'cellphone' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'base' => 'required|exists:bases,id',
                'career' => 'required|exists:careers,id',
                'emergency_contact' => 'required|string',
                'emergency_phone' => 'required|string',
                'emergency_direction' => 'required|string',
            ],
            [
                'register_date.required' => 'La fecha de registro es requerida',
                'register_date.date' => 'La fecha de registro no es válida',
                'name.required' => 'El nombre es requerido',
                'name.string' => 'El nombre no es válido',
                'last_names.required' => 'El apellido es requerido',
                'last_names.string' => 'El apellido no es válido',
                'curp.required' => 'La CURP es requerida',
                'curp.unique' => 'La CURP ya está en uso',
                'curp.string' => 'La CURP no es válida',
                'phone.required' => 'El teléfono es requerido',
                'phone.string' => 'El teléfono no es válido',
                'cellphone.required' => 'El celular es requerido',
                'cellphone.string' => 'El celular no es válido',
                'email.required' => 'El correo electrónico es requerido',
                'email.email' => 'El correo electrónico no es válido',
                'email.unique' => 'El correo electrónico ya está en uso',
                'base.required' => 'La base es requerida',
                'base.exists' => 'La base no existe',
                'career.required' => 'La carrera es requerida',
                'career.exists' => 'La carrera no existe',
                'emergency_contact.required' => 'El contacto de emergencia es requerido',
                'emergency_contact.string' => 'El contacto de emergencia no es válido',
                'emergency_phone.required' => 'El teléfono de emergencia es requerido',
                'emergency_phone.string' => 'El teléfono de emergencia no es válido',
                'emergency_direction.required' => 'La dirección de emergencia es requerida',
                'emergency_direction.string' => 'La dirección de emergencia no es válida',
            ]);

            if($validator->fails()){
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $student = new User();
            $student->created_at = $request->register_date;
            $student->name = $request->name;
            $student->last_names = $request->last_names;
            $student->curp = $request->curp;
            $student->phone = $request->phone;
            $student->cellphone = $request->cellphone;
            $student->email = $request->email;
            $student->id_base = $request->base;
            $student->id_carrier = $request->career;
            $student->emergency_contact = $request->emergency_contact;
            $student->emergency_phone = $request->emergency_phone;
            $student->emergency_direction = $request->emergency_direction;
            $student->user_type = 'student';
            $student->password = bcrypt($student->curp);
            $student->save();

            $base = Base::find($request->base);
            $student->user_identification = 'A' . $base->name[0] . $student->id;
            $student->save();

            return response()->json($student, 201);
        }catch(\Exception $e){
            return response()->json(["error" => $e -> getMessage()], 500);
        }
    }
}
