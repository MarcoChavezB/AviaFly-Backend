<?php

namespace App\Http\Controllers;

use App\Mail\AdminEntryNotification;
use App\Mail\EmployeeEntryNotification;
use App\Mail\FingerPrintMail;
use App\Models\CheckInRecords;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index(Request $request){

        $employees = DB::table('employees')
            ->join('bases', 'employees.id_base', '=', 'bases.id')
            ->select('employees.id', 'employees.name', 'employees.last_names', 'employees.user_identification', 'employees.user_type','bases.name as base')
            ->orderBy('employees.id', 'desc')
            ->get();

        return response()->json(['employees' => $employees]);
    }
    public function show($id){
        $employee = Employee::where('id', $id)->with('base:id,name')->first();

        if(!$employee){
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        return response()->json($employee);
    }

    public function update(Request $request, $id){
        $employee = Employee::where('id', $id)->first();


        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:employees,email,'.$employee->id,
            'company_email' => 'required|email|unique:employees,company_email,'.$employee->id,
            'curp' => 'required|string|unique:employees,curp,'.$employee->id,
            'name' => 'required|string',
            'last_names' => 'required|string',
            'phone' => 'required|string',
            'cellphone' => 'required|string',
        ],
        [
            'email.required' => 'El campo email es requerido',
            'email.email' => 'El campo email debe ser un correo válido',
            'email.unique' => 'El email ya está en uso',
            'company_email.required' => 'El campo correo de la empresa es requerido',
            'company_email.email' => 'El campo correo de la empresa debe ser un correo válido',
            'company_email.unique' => 'El correo de la empresa ya está en uso',
            'curp.required' => 'El campo CURP es requerido',
            'curp.string' => 'El campo CURP debe ser una cadena de texto',
            'curp.unique' => 'El CURP ya está en uso',
            'name.required' => 'El campo nombre es requerido',
            'name.string' => 'El campo nombre debe ser una cadena de texto',
            'last_names.required' => 'El campo apellidos es requerido',
            'last_names.string' => 'El campo apellidos debe ser una cadena de texto',
            'phone.required' => 'El campo teléfono es requerido',
            'phone.string' => 'El campo teléfono debe ser una cadena de texto',
            'cellphone.required' => 'El campo celular es requerido',
            'cellphone.string' => 'El campo celular debe ser una cadena de texto',

        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }


        if(!$employee){
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $employee->update($request->all());

        return response()->json(['message' => 'Empleado actualizado correctamente']);
    }

    public function updatePassword(Request $request, $id){
        $employee = Employee::where('id', $id)->first();

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'password_confirmation' => 'required|string|same:password',
        ],
        [
            'password.required' => 'El campo contraseña es requerida',
            'password.string' => 'El campo contraseña debe ser una cadena de texto',
            'password_confirmation.required' => 'La confirmación de contraseña es requerida',
            'password_confirmation.string' => 'El campo confirmación de contraseña debe ser una cadena de texto',
            'password_confirmation.same' => 'Las contraseñas no coinciden',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        if(!$employee){
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $user = User::where('user_identification', $employee->user_identification)->first();

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        $tokens = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    function fingerPrintList($id_finger){

        $employee = Employee::where('id', $id_finger)->first();

        if(!$employee){
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        if(CheckInRecords::where('id_employee', $employee->id)->where('arrival_date', date('Y-m-d'))->exists()){
            return response()->json(['message' => 'Ya se ha registrado la asistencia del dia de hoy'], 400);
        }

        CheckInRecords::Create([
            'arrival_date' => date('Y-m-d'),
            'arrival_time' => date('H:i:s'),
            'id_employee' => $employee->id
        ]);

        $employeeName = $employee->name . ' ' . $employee->last_names;
        $currentDateTime = date('Y-m-d H:i:s');
        $user_type = $employee->user_type;

        $admins = Employee::where('user_type', 'admin')->orWhere('user_type', 'root')->get();

        foreach($admins as $admin){
            Mail::to($admin->email)->send(new AdminEntryNotification($employeeName, $currentDateTime, $user_type));
        }

        Mail::to($employee->company_email)->send(new EmployeeEntryNotification($employeeName, $currentDateTime, $user_type));

        return response()->json(['message' => 'Correo enviado correctamente']);
    }
}
