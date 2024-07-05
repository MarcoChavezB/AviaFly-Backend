<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
}
