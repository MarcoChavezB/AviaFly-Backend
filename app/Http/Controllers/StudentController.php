<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\FlightPayment;
use App\Models\InfoFlight;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'register_date' => 'required|date',
                    'name' => 'required|string',
                    'last_names' => 'required|string',
                    'curp' => 'required|string|unique:students,curp',
                    'phone' => 'required|string',
                    'cellphone' => 'required|string',
                    'email' => 'required|email|unique:students,email',
                    'base' => 'required|exists:bases,id',
                    'career' => 'required|exists:careers,id',
                    'emergency_contact' => 'required|string',
                    'emergency_phone' => 'required|string',
                    'emergency_direction' => 'required|string',
                    'turn' => 'required|exists:turns,id',
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
                ]
            );

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $student = new Student();
            $student->name = $request->name;
            $student->last_names = $request->last_names;
            $student->curp = $request->curp;
            $student->phone = $request->phone;
            $student->cellphone = $request->cellphone;
            $student->email = $request->email;
            $student->id_base = $request->base;
            $student->emergency_contact = $request->emergency_contact;
            $student->emergency_phone = $request->emergency_phone;
            $student->emergency_direction = $request->emergency_direction;
            $student->start_date = $request->register_date;
            $student->id_career = $request->career;
            $student->user_identification = $request->curp;
            $student->save();

            $base = Base::find($request->base);
            $student->user_identification = 'A' . $base->name[0] . $student->id;
            $student->save();

            $careerSubjects = DB::table('career_subjects')
                ->where('id_career', $request->career)
                ->get();

            foreach ($careerSubjects as $careerSubject) {
                $teacher = DB::table('teacher_subject_turns')
                    ->where('id_subject', $careerSubject->id_subject)
                    ->where('id_turn', $request->turn)
                    ->join('employees', 'teacher_subject_turns.id_teacher', '=', 'employees.id')
                    ->where('employees.id_base', $student->id_base)
                    ->first();

                if ($teacher) {
                    DB::table('student_subjects')->insert([
                        'id_student' => $student->id,
                        'id_subject' => $careerSubject->id_subject,
                        'id_turn' => $request->turn,
                        'id_teacher' => $teacher->id_teacher,
                    ]);
                } else {
                    $student->delete();
                    return response()->json(["errors" => ["No se encontró un profesor para la materia " . $careerSubject->id_subject]], 400);
                }
            }

            $career = DB::table('careers')->find($request->career);

            $startDate = Carbon::parse($request->register_date);

            for ($i = 0; $i < $career->monthly_payments; $i++) {
                $paymentDate = clone $startDate;
                $paymentDate->addMonths($i + 1);

                DB::table('monthly_payments')->insert([
                    'id_student' => $student->id,
                    'payment_date' => $paymentDate,
                    'amount' => $career->monthly_fee,
                    'status' => 'pending',
                ]);
            }

            $user = new User();
            $user->user_identification = $student->user_identification;
            $user->password = bcrypt($student->curp);
            $user->user_type = 'student';
            $user->save();

            return response()->json($student, 201);
        } catch (\Exception $e) {
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }

    public function getStudents()
    {
        try {

            //$user = auth()->user(); //Sacar el id de la base del usuario logueado

            $base = Base::where('id', 1)
                ->first(['id', 'name']); //Torreón

            if (!$base) {
                return response()->json(["errors" => ["No hay bases creadas o no se encontro la base del usuario auth"]], 404);
            }

            if ($base->name == 'Torreón') {
                $students = Student::all(['id', 'name', 'last_names', 'user_identification']);

                if ($students->isEmpty()) {
                    return response()->json(["errors" => ["No hay estudiantes creados"]], 404);
                }

                return response()->json(['students' => $students], 200);
            } else {
                $students = Student::where('id_base', $base->id)->get(['id', 'name', 'last_names', 'user_identification']);

                if ($students->isEmpty()) {
                    return response()->json(["errors" => ["No hay estudiantes creados"]], 400);
                }

                return response()->json(['students' => $students], 200);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return response()->json(["error" => "Estudiante no encontrado"], 404);
            }

            $career = DB::table('careers')
                ->where('id', $student->id_career)
                ->first(['name']);

            $subjects = DB::table('student_subjects')
                ->where('student_subjects.id_student', $id)
                ->join('subjects', 'student_subjects.id_subject', '=', 'subjects.id')
                ->join('employees', 'student_subjects.id_teacher', '=', 'employees.id')
                ->select(
                    'subjects.name as subject_name',
                    'subjects.id as subject_id',
                    'student_subjects.final_grade as grade',
                    DB::raw('CONCAT(employees.name, " ", employees.last_names) as teacher_full_name'),
                    'employees.id as teacher_id',
                    'employees.user_identification as teacher_identification',
                    'student_subjects.updated_at as last_update',
                    'student_subjects.start_date as start_date',
                    'student_subjects.end_date as end_date',
                    'student_subjects.duration as duration'
                )
                ->get();

            $student->career_name = $career->name;
            $student->makeHidden(['id_created_by', 'id_history_flight', 'created_at', 'updated_at']); //8714936204

            return response()->json(['student' => $student, 'student_subjects' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    //Abajo de 85 es reprobado
    public function updateGrade(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'id_student' => 'required|exists:students,id',
                    'id_subject' => 'required|exists:subjects,id',
                    'final_grade' => 'sometimes|numeric|min:0|max:100',
                    'start_date' => 'sometimes|date',
                    'end_date' => 'sometimes|date',
                    'duration' => 'sometimes|numeric|min:0',
                ],
                [
                    'student_id.required' => 'El id del estudiante es requerido',
                    'student_id.exists' => 'El id del estudiante no existe',
                    'subject_id.required' => 'El id de la materia es requerido',
                    'subject_id.exists' => 'El id de la materia no existe',
                    'final_grade.numeric' => 'La calificación no es válida',
                    'final_grade.min' => 'La calificación no puede ser menor a 0',
                    'final_grade.max' => 'La calificación no puede ser mayor a 100',
                    'start_date.date' => 'La fecha de inicio no es válida',
                    'end_date.date' => 'La fecha de fin no es válida',
                    'duration.numeric' => 'La duración no es válida',
                ]
            );

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $studentSubject = StudentSubject::where('id_student', $request->id_student)
                ->where('id_subject', $request->id_subject)
                ->first();

            if (!$studentSubject) {
                return response()->json(["errors" => ["El estudiante no está inscrito en la materia", $request->all()]], 404);
            }

            $studentSubject->update($request->all());

            return response()->json(["message" => "Calificación actualizada"], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }

    public function indexSimulator(string $name = null)
    {
        $client = Auth::user();
        $id_base = Employee::where('user_identification', $client->user_identification)->first()->id_base;

        $data = DB::select("
SELECT
  students.id,
  students.name,
  students.last_names,
  careers.name AS career_name,
  students.start_date,
  MAX(CASE
    WHEN student_subjects.status = 'failed' OR student_subjects.status = 'pending' THEN 1
    ELSE 0
  END) AS subjects_failed,
  MAX(CASE
    WHEN flight_payments.status = 'pending' THEN 1
    ELSE 0
  END) AS pendings_payments,
  MAX(CASE
    WHEN monthly_payments.status = 'pending' OR monthly_payments.status = 'owed' THEN 1
    ELSE 0
  END) AS pendings_months
FROM
  students
  LEFT JOIN careers ON students.id_career = careers.id
  LEFT JOIN student_subjects ON students.id = student_subjects.id_student
  LEFT JOIN flight_payments ON students.id = flight_payments.id_student
  LEFT JOIN flight_history ON flight_payments.id_flight = flight_history.id
  LEFT JOIN monthly_payments ON monthly_payments.id_student = students.id
WHERE
            students.id_base = $id_base
AND students.name LIKE '%$name%'
GROUP BY
  students.id,
  students.name,
  students.last_names,
  careers.name,
  students.start_date;
        ");
        return response()->json($data);
    }


    function getStudentSimulatorByName(string $name)
    {
        return $this->indexSimulator($name);
    }

    public function getInfoVueloAlumno(int $id)
    {
        if ($id == null) {
            return response()->json(["error" => "No se ha proporcionado un id"], 400);
        }

        $client = Auth::user();
        $id_base = Employee::where('user_identification', $client->user_identification)->first()->id_base;

        // Obtener información de los estudiantes
        $students = Student::select(
            'students.id',
            'students.credit',
            'students.name',
            'students.last_names',
            'students.start_date',
            'careers.name as career_name',
            DB::raw('COALESCE(AVG(student_subjects.final_grade), 0) AS average')
        )
            ->leftJoin('careers', 'students.id_career', '=', 'careers.id')
            ->leftJoin('student_subjects', 'students.id', '=', 'student_subjects.id_student')
            ->where('students.id_base', $id_base)
            ->where('students.id', $id)
            ->groupBy('students.id', 'students.name', 'students.last_names', 'students.credit', 'students.start_date', 'careers.name')
            ->get();

        // Obtener las horas de vuelo
        foreach ($students as $student) {
            $student->hours = DB::table('flight_history')
                ->join('flight_payments', 'flight_history.id', '=', 'flight_payments.id_flight')
                ->where('flight_payments.id_student', $student->id)
                ->select('flight_history.hours', 'flight_history.type_flight', 'flight_history.flight_date')
                ->get();
        }

        // Obtener las horas totales
        $totalHours = DB::table('students')
            ->leftJoin('flight_payments', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('students.id', $id)
            ->select(DB::raw('SUM(flight_history.hours) AS total_hours'))
            ->groupBy('students.id', 'students.name')
            ->first();

        // Obtener las horas por categoría de vuelo
        $flightCategoryHours = DB::table('students')
            ->leftJoin('flight_payments', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('students.id', $id)
            ->select(
                DB::raw('SUM(CASE WHEN flight_history.type_flight = "simulator" THEN flight_history.hours ELSE 0 END) AS simulator_hours'),
                DB::raw('SUM(CASE WHEN flight_history.type_flight = "monomotor" THEN flight_history.hours ELSE 0 END) AS monomotor_hours'),
                DB::raw('SUM(CASE WHEN flight_history.type_flight = "multimotor" THEN flight_history.hours ELSE 0 END) AS multimotor_hours')
            )
            ->groupBy('students.name')
            ->first();

        $isDebt = $this->is_debt($id);

        return response()->json([
            'students' => $students,
            'total_hours' => $totalHours,
            'flight_category_hours' => $flightCategoryHours,
            'is_debt' => $isDebt
        ], 200);
    }


    function is_debt(int $id)
    {
        $value = DB::select("
            SELECT
              MAX(CASE WHEN student_subjects.status = 'failed' OR student_subjects.status = 'pending' THEN 1 ELSE 0 END) AS subjects_failed,
              MAX(CASE WHEN flight_payments.status = 'pending' THEN 1 ELSE 0 END) AS pendings_payments,
              MAX(CASE WHEN monthly_payments.status = 'pending' OR monthly_payments.status = 'owed' THEN 1 ELSE 0 END) AS pendings_months
            FROM
             students
              LEFT JOIN careers ON students.id_career = careers.id
              LEFT JOIN student_subjects ON students.id = student_subjects.id_student
              LEFT JOIN flight_payments ON students.id = flight_payments.id_student
              LEFT JOIN flight_history ON flight_payments.id_flight = flight_history.id
              LEFT JOIN monthly_payments ON monthly_payments.id_student = students.id
                WHERE students.id_base = 1 and students.id = $id
            GROUP BY students.id, students.name, students.last_names, careers.name, students.start_date;
            ");

        if ($value[0]->pendings_payments == 1 || $value[0]->pendings_months == 1 || $value[0]->subjects_failed == 1) {
            return true;
        } else {
            return false;
        }
    }

    function storeFlight(Request $request)
    {
        $client = Auth::user();
        $validator = Validator::make($request->all(), [
            'id_student' => 'required|numeric',
            'id_instructor' => 'required|numeric',
            'flight_type' => 'required|string',
            'flight_date' => 'required|date',
            'flight_payment_status' => 'required|string',
            'hours' => 'required|numeric',
            'total' => 'required|numeric',
            'pay_method' => 'required|string',
            'due_week' => 'nullable|numeric',
            'installment_value' => 'nullable|numeric'
        ], [
            'id_student.required' => 'El id del usuario es requerido',
            'id_instructor.required' => 'El id del instructor es requerido',
            'flight_type.required' => 'El tipo de vuelo es requerido',
            'flight_date.required' => 'La fecha de vuelo es requerida',
            'flight_date.date' => 'La fecha de vuelo no es válida',
            'flight_payment_status.required' => 'El estatus de pago es requerido',
            'hours.required' => 'Las horas de vuelo son requeridas',
            'hours.numeric' => 'Las horas de vuelo no son válidas',
            'total.required' => 'El total es requerido',
            'total.numeric' => 'El total no es válido',
            'pay_method.required' => 'El método de pago es requerido',
            'due_week.numeric' => 'La semana de vencimiento no es válida',
            'installment_value.numeric' => 'El valor de la mensualidad no es válido',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }
        
        $empleado = Employee::find($request->id_instructor);
        if($empleado->user_type != 'instructor'){
            return response()->json(["error" => "El empleado no es un instructor"], 400);
        }
        
        $student = Student::find($request->id_student);
        $hoursCredit = $this->getPriceFly($request->flight_type) * $request->hours;
        if($student->credit < $hoursCredit){
            return response()->json(["error" => "El estudiante no tiene suficientes créditos"], 400);
        }
       
        DB::statement('CALL agendarHorasSimulador(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $request->id_student,
            Auth::user()->id,
            $request->id_instructor,
            $request->flight_type,
            $request->flight_date,
            $request->flight_payment_status,
            $request->hours,
            $request->total,
            $request->pay_method,
            $request->due_week,
            $request->installment_value
        ]);

        $message = $request->flight_payment_status == 'pending' ? 'Vuelo agendado, pendiente de pago' : 'Se agendo el vuelo';
        return response()->json(["message" => $message], 201);
    }
    
    function hasCredit(int $id, int $hours, string $flight_type){
        $student = Student::find($id);
        $restantCredit = $student->credit - $hours * $this->getPriceFly($flight_type);
        $optimCredit = $this->getPriceFly($flight_type) * 2;
        if($restantCredit < $optimCredit || $restantCredit < 0 ){
            return false;
        }
        return true;
    }
    
    function getPriceFly(string $name){
        return InfoFlight::where('flight_type', $name)->value('price');
    }
}