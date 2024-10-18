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
    Log::channel('slack')->error("Se realizó la petición para el empleado " . "$id_finger");
    $currentDay = Carbon::now()->dayOfWeekIso; // Día de la semana (1 = lunes, 7 = domingo)
    $currentDate = Carbon::now()->format('Y-m-d');
    $currentTime = Carbon::now(); // Obtener objeto Carbon para la hora actual

    $employee = Employee::find($id_finger);
    if (!$employee) {
        return response()->json(['message' => 'Empleado no encontrado'], 414);
    }

    // Obtener los registros del día actual
    $todayRecords = CheckInRecords::where('id_employee', $employee->id)
                                  ->where('arrival_date', $currentDate)
                                  ->orderBy('arrival_time', 'desc')
                                  ->get();

    // Si hay registros, validar el tiempo desde el último registro
    if ($todayRecords->isNotEmpty()) {
        $lastRecord = $todayRecords->first(); // Último registro

        // Combina la fecha actual con el tiempo del último registro
        $lastRecordTime = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate . ' ' . $lastRecord->arrival_time);

        // Validar si el último registro fue en los últimos 10 minutos
        if ($currentTime->diffInMinutes($lastRecordTime) < 10) {
            return response()->json(['message' => 'Ya se ha registrado recientemente. Debes esperar al menos 10 minutos.'], 400);
        }
    }

    // Determinar el tipo de registro
    if ($todayRecords->isEmpty()) {
        // Primer registro del día, debe ser entrada
        $type = 'entrada';
    } else {
        // Obtenemos el último registro
        $lastType = $todayRecords->first()->type;

        if ($lastType === 'entrada') {
            // Si el último registro fue una entrada, se puede registrar una hora de comida
            $type = 'hora de comida';
        } elseif ($lastType === 'hora de comida') {
            // Si el último registro fue una hora de comida, se puede registrar un fin de hora de comida
            $type = 'fin de hora de comida';
        } elseif ($lastType === 'fin de hora de comida') {
            // Permitir la salida a partir de la hora indicada
            if (
                ($currentDay >= 1 && $currentDay <= 5 && $currentTime->format('H:i:s') >= '18:00:00') || // Lunes a viernes a partir de las 16:00
                ($currentDay == 6 && $currentTime->format('H:i:s') >= '13:00:00') // Sábados a partir de las 13:00
            ) {
                $type = 'salida';
            } else {
                // Si no se cumple la condición para salida, se puede registrar otra hora de comida
                $type = 'hora de comida'; // Permitir registrar más horas de comida
            }
        } elseif ($lastType === 'salida') {
            return response()->json(['message' => 'No se puede registrar después de una salida.'], 410);
        }
    }

    // Registrar el nuevo tipo de asistencia
    CheckInRecords::create([
        'arrival_date' => $currentDate,
        'arrival_time' => $currentTime->format('H:i:s'), // Asegúrate de guardar solo la hora
        'id_employee' => $employee->id,
        'type' => $type
    ]);

    // Enviar correos para la "entrada"
    if ($type === 'entrada') {
        $employeeName = $employee->name . ' ' . $employee->last_names;
        $currentDateTimeFormatted = $currentTime->format('Y-m-d H:i:s');
        $user_type = $employee->user_type;

        $admins = Employee::whereIn('user_type', ['admin', 'root'])->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new AdminEntryNotification($employeeName, $currentDateTimeFormatted, $user_type, $type));
        }

        Mail::to($employee->email)->send(new EmployeeEntryNotification($employeeName, $currentDateTimeFormatted, $user_type, $type));
    }

    return response()->json(['message' => 'Registro de asistencia exitoso']);
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
