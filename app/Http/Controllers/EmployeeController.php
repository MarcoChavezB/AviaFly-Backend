<?php

namespace App\Http\Controllers;

use App\Mail\AdminEntryNotification;
use App\Mail\EmployeeEntryNotification;
use App\Models\CheckInRecords;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            return response()->json(['message' => 'Empleado no encontrado'], 414);
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
            return response()->json(["errors" => $validator->errors()], 410);
        }

        if(!$employee){
            return response()->json(['message' => 'Empleado no encontrado'], 414);
        }

        $employee->update($request->all());

        return response()->json(['message' => 'Empleado actualizado correctamente']);
    }

    public function updatePassword(Request $request, $id){
        try{
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
                return response()->json(["errors" => $validator->errors()], 410);
            }

            if(!$employee){
                return response()->json(['message' => 'Empleado no encontrado'], 414);
            }

            $user = User::where('user_identification', $employee->user_identification)->first();

            DB::transaction(function() use ($user, $request){
                $user->update([
                    'password' => bcrypt($request->password)
                ]);
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
            });

            return response()->json(['message' => 'Contraseña actualizada correctamente']);
        }catch(\Exception $e){
            return response()->json(['message' => 'Internal Server Error'], 510);
        }
    }

    public function fingerPrintList($id_finger)
    {

        Log::channel('slack')->error("se realizo la peticion para el empleado" . "$id_finger");
        $day = date('N');
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        if (($day == 16 && ($currentTime < '08:00:00' || $currentTime > '14:00:00')) || $day == 7) {
            return response()->json(['message' => 'Fuera de horario laboral'], 410);
        }

        if ($day != 16 && ($currentTime < '08:00:00' || $currentTime > '17:00:00')) {
            return response()->json(['message' => 'Fuera de horario laboral'], 410);
        }

        $employee = Employee::find($id_finger);

        if (!$employee) {
            return response()->json(['message' => 'Empleado no encontrado'], 414);
        }


        if (CheckInRecords::where('id_employee', $employee->id)->where('arrival_date', date('Y-m-d'))->count() >= 4) {
            return response()->json(['message' => 'Ya se ha registrado la asistencia del día de hoy'], 400);
        }

        $todayRecords = CheckInRecords::where('id_employee', $employee->id)
                                       ->where('arrival_date', $currentDate)
                                       ->orderBy('arrival_time', 'desc')
                                       ->get();

        if ($todayRecords->isNotEmpty()) {
            $lastRecord = $todayRecords->first();
            $lastArrivalTime = $lastRecord->arrival_time;

            $lastArrivalDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $currentDate . ' ' . $lastArrivalTime);
            $currentDateTime = \Carbon\Carbon::now();

            if ($currentDateTime->diffInMinutes($lastArrivalDateTime) < 1) {
                return response()->json(['message' => 'Ya se ha registrado asistencia recientemente. Espera 20 minutos.'], 400);
            }
        }

        // entrada / hora de comida / fin de hora de comida / salid

        if ($todayRecords->isEmpty()) {
            $type = 'entrada';
        } elseif ($todayRecords->first()->type == 'entrada') {
            $type = 'hora de comida';
        } elseif ($todayRecords->first()->type == 'hora de comida') {
            $type = 'fin de hora de comida';
        } elseif ($todayRecords->first()->type == 'fin de hora de comida' || Carbon::now()->format('H:i:s') > '20:00:00') {
            $type = 'salida';
        } else {
            return response()->json(['message' => $todayRecords->first()], 410);
        }

        CheckInRecords::create([
            'arrival_date' => $currentDate,
            'arrival_time' => $currentTime,
            'id_employee' => $employee->id,
            'type' => $type
        ]);


        if($type === 'entrada' ){

            $employeeName = $employee->name . ' ' . $employee->last_names;
            $currentDateTimeFormatted = date('Y-m-d H:i:s');
            $user_type = $employee->user_type;

            $admins = Employee::whereIn('user_type', ['admin', 'root'])->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new AdminEntryNotification($employeeName, $currentDateTimeFormatted, $user_type, $type));
            }

            Mail::to($employee->email)->send(new EmployeeEntryNotification($employeeName, $currentDateTimeFormatted, $user_type, $type));
        }

        return response()->json(['message' => 'Registro de asistencia y correos enviados correctamente']);
    }

    public function deleteAccessUser($id){

        try {
            $employee = Employee::where('id', $id)->first();

            if(!$employee){
                return response()->json(['message' => 'Empleado no encontrado'], 414);
            }

            $user = User::where('user_identification', $employee->user_identification)->first();

            if(!$user){
                return response()->json(['errors' => ['El usuario no tiene acceso']], 414);
            }

            DB::transaction(function() use ($user){
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
                $user->delete();
            });

            return response()->json(['message' => 'Accesos eliminados correctamente']);

        }catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 510);
        }
    }

    public function createAccessUser($id){
        try {

            $employee = Employee::where('id', $id)->first();

            if(!$employee){
                return response()->json(['message' => 'Empleado no encontrado'], 414);
            }

            $user = User::where('user_identification', $employee->user_identification)->first();

            if($user){
                return response()->json(['errors' => ['El usuario ya tiene acceso']], 410);
            }

            $user = User::create([
                'user_identification' => $employee->user_identification,
                'password' => bcrypt($employee->curp),
                'user_type' => $employee->user_type,
                'id_base' => $employee->id_base,
            ]);

            return response()->json(['message' => 'Acceso creado correctamente']);

        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 510);
        }
    }
}
