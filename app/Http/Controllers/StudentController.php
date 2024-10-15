<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStudentCreation;
use App\Models\Base;
use App\Models\Employee;
use App\Models\flightHistory;
use App\Models\InfoFlight;
use App\Models\Payments;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class StudentController extends Controller
{

    private $payment_method_controller;

    public function __construct(PaymentMethodController $payment_method_controller)
    {
        $this->payment_method_controller = new PaymentMethodController();
    }

    function index(string $identificator = null)
    {
        $client = Auth::user();
        $id_base = Employee::where('user_identification', $client->user_identification)->first()->id_base;

        $studens = Student::select('students.email','students.phone','students.user_identification','students.id', 'students.name', 'students.last_names', 'students.curp', 'students.flight_credit', 'careers.name as career_name')
            ->leftJoin('careers', 'students.id_career', '=', 'careers.id')
            ->where('careers.name', 'Piloto privado')
            ->where('students.id_base', $id_base)
            ->where('students.name', 'like', "%$identificator%")
            ->groupBy('students.email','students.phone','students.user_identification', 'students.id', 'students.name', 'students.last_names', 'students.curp', 'students.flight_credit', 'careers.name')
            ->get();

        return response()->json($studens, 200);
    }


    function indexId(string $identificator)
    {
        $student = Student::select('id', 'name', 'last_names', 'email', 'phone', 'curp', 'credit', 'flight_credit', 'user_identification', 'emergency_direction')
                          ->where('id', $identificator)
                          ->first();

        if ($student) {
            return response()->json([$student], 200);
        } else {
            return response()->json(['error' => 'Student not found'], 404);
        }
    }
   function indexByName(string $name)
    {
        return $this->index($name);
    }
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
            $student->curp = strtoupper($request->curp);
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
            $student->credit = 0;
            $student->save();

            $base = Base::find($request->base);
            $student->user_identification = 'A' . $base->name[0] . $student->id;
            $student->save();

            $baseName = strtolower($base->name);
            $baseName = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $baseName);
            $folderPath = public_path("bases/{$baseName}/{$student->user_identification}");
            $vouchersPath = $folderPath . '/vouchers';
            $ticketsPath = $folderPath . '/tickets';

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
                mkdir($vouchersPath, 0777, true);
                mkdir($ticketsPath, 0777, true);
            }

            $user = new User();
            $user->user_identification = $student->user_identification;
            $user->password = bcrypt($student->curp);
            $user->user_type = 'student';
            $user->id_base = $request->base;
            $user->save();


            ProcessStudentCreation::dispatchAfterResponse($student, $request->all());

            return response()->json($user->user_identification, 201);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function getStudents(Request $request)
    {
        try {
            $user = Auth::user();
            $base = Base::where('id', $user->id_base)->first(['id', 'name']);

            $query = DB::table('students')
                ->join('bases', 'students.id_base', '=', 'bases.id')
                ->join('careers', 'students.id_career', '=', 'careers.id')
                ->select('students.id', 'students.name', 'students.last_names', 'students.user_identification', 'careers.name as career_name', 'bases.name as base_name')
                ->orderBy('students.id', 'desc');

            if ($base->name != 'Torreón') {
                $query->where('students.id_base', $user->id_base);
            }

            $searchString = $request->input('searchStr');
            if ($searchString) {
                $query->where(function ($query) use ($searchString) {
                    $query->where('students.name', 'like', '%' . $searchString . '%')
                        ->orWhere('students.last_names', 'like', '%' . $searchString . '%')
                        ->orWhere('students.user_identification', 'like', '%' . $searchString . '%')
                        ->orWhere(DB::raw("CONCAT(students.name, ' ', students.last_names)"), 'like', '%' . $searchString . '%');
                });
            }


            $students = $query->paginate(55);

            $paginationData = [
                'current_page' => $students->currentPage(),
                'total_pages' => $students->lastPage(),
                'has_next_page' => $students->hasMorePages(),
                'on_this_page' => $students->count(),
                ];

            return response()->json([
                'students' => $students->items(),
                'pagination' => $paginationData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => 'Internal Server Error'], 500);
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
                )
                ->get();

            $student->career_name = $career->name;
            $student->makeHidden(['id_created_by', 'id_history_flight', 'created_at', 'updated_at']);

            return response()->json(['student' => $student, 'student_subjects' => $subjects], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function updateGrade(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'id_student' => 'required|exists:students,id',
                    'id_subject' => 'required|exists:subjects,id',
                    'final_grade' => 'sometimes|numeric|min:0|max:100',
                ],
                [
                    'student_id.required' => 'El id del estudiante es requerido',
                    'student_id.exists' => 'El id del estudiante no existe',
                    'subject_id.required' => 'El id de la materia es requerido',
                    'subject_id.exists' => 'El id de la materia no existe',
                    'final_grade.numeric' => 'La calificación no es válida',
                    'final_grade.min' => 'La calificación no puede ser menor a 0',
                    'final_grade.max' => 'La calificación no puede ser mayor a 100',
                ]
            );

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            DB::transaction(function () use ($request) {
                $studentSubject = StudentSubject::where('id_student', $request->id_student)
                    ->where('id_subject', $request->id_subject)
                    ->first();

                if (!$studentSubject) {
                    return response()->json(["errors" => ["El estudiante no está inscrito en la materia", $request->all()]], 404);
                }

                $studentSubject->update($request->all());
                $studentSubject->status = $request->final_grade >= 85 ? 'approved' : 'failed';
                $studentSubject->save();

                return response()->json(["message" => "Calificación actualizada"], 200);
            });
        } catch (\Exception $e) {
            return response()->json(["error" => "Error al actualizar la calificación: " . $e->getMessage()], 500);
        }
    }


    /*
    Validada por bases
 */
    public function indexSimulator(string $name = null)
    {
        $client = Auth::user();
        $id_base = Employee::where('user_identification', $client->user_identification)->first()->id_base;

        $data = DB::select("
                SELECT
                students.id, students.name, students.last_names, careers.name AS career_name, students.start_date,
                    MAX(CASE WHEN student_subjects.status = 'failed' OR student_subjects.status = 'pending' THEN 1 ELSE 0 END) AS subjects_failed,
                    MAX(CASE WHEN flight_payments.payment_status = 'pending' THEN 1 ELSE 0 END) AS pendings_payments,
                    MAX(CASE WHEN monthly_payments.status = 'pending' OR monthly_payments.status = 'owed' THEN 1 ELSE 0 END) AS pendings_months
                FROM students
                LEFT JOIN careers ON students.id_career = careers.id
                LEFT JOIN student_subjects ON students.id = student_subjects.id_student
                LEFT JOIN flight_payments ON students.id = flight_payments.id_student
                LEFT JOIN flight_history ON flight_payments.id_flight = flight_history.id
                LEFT JOIN monthly_payments ON monthly_payments.id_student = students.id
                WHERE
                students.id_base = $id_base
                AND students.name LIKE '%$name%'
                AND careers.name = 'Piloto privado'
                GROUP BY students.id, students.name, students.last_names, careers.name, students.start_date;");
        return response()->json($data);
    }

    /*
    Buscador de alumnos en vista vuelos
    usa la funcion indexSimulator
*/
    function getStudentSimulatorByName(string $name)
    {
        return $this->indexSimulator($name);
    }

    /*
    Obtiene reporte de un alumno
    Validada por base
      subjects_failed: number,
      pendings_payments: number,
      pendings_months: number
    */
public function getInfoVueloAlumno(int $id = null)
{
    $client = Auth::user();
    $userController = new UserController();
    $id_base = $userController->getBaseAuth($client)->id;
    // id de piloto
    // Construcción dinámica de la consulta SQL
    $query = "
        SELECT
            students.id, students.name, students.last_names, careers.name AS career_name, students.start_date, students.user_identification,
            MAX(CASE WHEN student_subjects.status = 'failed' OR student_subjects.status = 'pending' THEN 1 ELSE 0 END) AS subjects_failed,
            MAX(CASE WHEN flight_payments.payment_status = 'pending' THEN 0 ELSE 0 END) AS pendings_payments,
            MAX(CASE WHEN monthly_payments.status = 'pending' OR monthly_payments.status = 'owed' THEN 0 ELSE 0 END) AS pendings_months
        FROM students
        LEFT JOIN careers ON students.id_career = careers.id
        LEFT JOIN student_subjects ON students.id = student_subjects.id_student
        LEFT JOIN flight_payments ON students.id = flight_payments.id_student
        LEFT JOIN flight_history ON flight_payments.id_flight = flight_history.id
        LEFT JOIN monthly_payments ON monthly_payments.id_student = students.id
        WHERE students.id_base = :id_base
            AND students.id_career = 2
    ";

    $bindings = ['id_base' => $id_base];

    // Añadir condición para el ID si se proporciona
    if ($id !== null) {
        $query .= " AND students.id = :id";
        $bindings['id'] = $id;
    }

    $query .= " GROUP BY students.id, students.name, students.last_names, careers.name, students.start_date, students.user_identification";

    // Ejecutar la consulta
    $dataQuery = DB::select($query, $bindings);

    // Preparar la respuesta con la información de cada estudiante
    $studentsData = [];

    foreach ($dataQuery as $student) {
        // Obtener las horas de vuelo para el estudiante actual
        $hours = DB::table('flight_history')
            ->join('flight_payments', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->where('flight_payments.id_student', $student->id)
            ->select('flight_history.hours', 'flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_status')
            ->get();

        // Determinar si el estudiante tiene deuda
        $has_debt = $this->is_debt($student->id);

        // Obtener las horas totales para el estudiante actual
        $totalHours = DB::table('students')
            ->leftJoin('flight_payments', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('students.id', $student->id)
            ->where('flight_history.flight_status', 'hecho')
            ->select(DB::raw('SUM(flight_history.hours) AS total_hours'))
            ->first();
        $totalHours = $totalHours ? $totalHours->total_hours : '0.00';

        // Obtener las horas por categoría de vuelo para el estudiante actual
        $flightCategoryHours = DB::table('students')
            ->leftJoin('flight_payments', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('students.id', $student->id)
            ->where('flight_history.flight_status', 'hecho')
            ->select(
                DB::raw('SUM(CASE WHEN flight_history.type_flight = "simulador" THEN flight_history.hours ELSE 0 END) AS simulator_hours'),
                DB::raw('SUM(CASE WHEN flight_history.type_flight = "vuelo" THEN flight_history.hours ELSE 0 END) AS vuelo_hours')
            )
            ->first();
        $flightCategoryHours = $flightCategoryHours ? (object) [
            'simulator_hours' => $flightCategoryHours->simulator_hours ?? '0.00',
            'vuelo_hours' => $flightCategoryHours->vuelo_hours ?? '0.00',
        ] : (object) [
            'simulator_hours' => '0.00',
            'vuelo_hours' => '0.00',
        ];

        $studentsData[] = [
            'id' => $student->id,
            'user_identification' => $student->user_identification,
            'flight_credit' => '0.00',
            'name' => $student->name,
            'last_names' => $student->last_names,
            'start_date' => $student->start_date,
            'career_name' => $student->career_name,
            'subjects_failed' => $student->subjects_failed,
            'pendings_payments' => $student->pendings_payments,
            'pendings_months' => $student->pendings_months,
            'hours' => $hours,
            'total_hours' => [
                'total_hours' => $totalHours == null ? '0.00' : $totalHours
            ],
            'flight_category_hours' => [
                'simulator_hours' => $flightCategoryHours->simulator_hours,
                'vuelo_hours' => $flightCategoryHours->vuelo_hours,
            ],
            'is_debt' => $has_debt
        ];
    }

    return response()->json([
        'students' => $studentsData
    ], 200);
}

   /*
    Funcion para validar si un alumno tiene deuda
    retorna true si tiene deuda
*/
    function is_debt(int $id)
    {
        $value = DB::select("
            SELECT
              MAX(CASE WHEN student_subjects.status = 'failed' OR student_subjects.status = 'pending' THEN 1 ELSE 0 END) AS subjects_failed,
              MAX(CASE WHEN flight_payments.payment_status = 'pendiente' THEN 0 ELSE 0 END) AS pendings_payments,
              MAX(CASE WHEN monthly_payments.status = 'pending' OR monthly_payments.status = 'owed' THEN 0 ELSE 0 END) AS pendings_months
            FROM
             students
              LEFT JOIN careers ON students.id_career = careers.id
              LEFT JOIN student_subjects ON students.id = student_subjects.id_student
              LEFT JOIN flight_payments ON students.id = flight_payments.id_student
              LEFT JOIN flight_history ON flight_payments.id_flight = flight_history.id
              LEFT JOIN monthly_payments ON monthly_payments.id_student = students.id
                WHERE students.id = $id
            GROUP BY students.id, students.name, students.last_names, careers.name, students.start_date;
            ");
        if ($value[0]->pendings_payments == 1 || $value[0]->pendings_months == 1 || $value[0]->subjects_failed == 1) {
            return true;
        } else {
            return false;
        }
    }

    /*
    funcion para agendar un vuelo a un alumno
*/
    function storeFlight(Request $request)
    {
        $user = Employee::where('user_identification', Auth::user()->user_identification)->first();

        $validator = Validator::make($request->all(), [
            'id_instructor' => 'required|numeric|exists:employees,id',
            'flight_date' => 'required|string',
            'flight_hour' => 'required|string',
            'equipo' => 'required|string|exists:info_flights,id',
            'hours' => 'required|numeric',
            'flight_type' => 'required|string|in:simulador,vuelo',
            'flight_category' => 'required|string|in:VFR,IFR,IFR_nocturno',
            'maneuver' => 'required|string|in:local,ruta',
            'total' => 'required|numeric',
            'hour_instructor_cost' => 'required|numeric',
            'id_pay_method' => 'required|exists:payment_methods,id',
            'due_week' => 'nullable|numeric',
            'installment_value' => 'nullable|numeric',
            'id_student' => 'required|numeric',
            'flight_payment_status' => 'required|string|in:pendiente,pagado,cancelado',

        ], [
            'flight_airplane' => 'required|string',
            'id_student.required' => 'campo requerido',
            'id_instructor.exists' => 'Selecciona un instructor',
            'id_instructor.required' => 'campo requerido',
            'flight_type.required' => 'campo requerido',
            'flight_type.in' => 'El tipo de vuelo no es válido',
            'flight_date.required' => 'campo requerido',
            'flight_date.date' => 'La fecha de vuelo no es válida',
            'flight_hour.required' => 'campo requerido',
            'flight_hour.string' => 'La hora de vuelo no es válida',
            'flight_payment_status.required' => 'campo requerido',
            'flight_payment_status.in' => 'El estatus de pago no es válido',
            'hours.required' => 'campo requerido',
            'hours.numeric' => 'Las horas de vuelo no son válidas',
            'total.required' => 'campo requerido',
            'total.numeric' => 'campo requerido',
            'id_pay_method.required' => 'campo requerido',
            'due_week.numeric' => 'La semana de vencimiento no es válida',
            'installment_value.numeric' => 'El valor de la mensualidad no es válido',
            'equipo.required' => 'El equipo es requerido',
            'equipo.in' => 'El equipo no es válido',
            'flight_category.required' => 'campo requerido',
            'flight_category.in' => 'La categoría de vuelo no es válida',
            'maneuver.required' => 'campo requerido',
            'maneuver.in' => 'campo no válido',
            'hour_instructor_cost.numeric' => 'El costo de la hora de instructor no es válido',
            'flight_airplane.required' => 'campo requerido',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        /* if ($this->OtherFlightReserved($request->flight_date, $request->flight_hour, $request->hours, $request->flight_type)) {
            return response()->json(["errors" => ["sameDate" => ["Existe un vuelo en la fecha y hora por favor de seleccionar otra hora"]]], 400);
        } */

        if ($this->isDateRestricted($request->flight_date, $request->flight_hour, $request->hours, $request->equipo)) {
            return response()->json(["errors" => ["La fecha seleccionada coincide con una fecha inhabil. Por favor, selecciona otra hora."]], 400);
        }
        $empleado = Employee::find($request->id_instructor);

        $student = Student::find($request->id_student);
        if ($request->id_pay_method == $this->payment_method_controller->getCreditoVueloId()) {
            $hoursCredit = $this->getPriceFly($request->flight_type) * $request->hours;
            if ($student->flight_credit < $hoursCredit) {
                return response()->json(["errors" => ["El estudiante no tiene suficientes créditos"]], 400);
            }
        }

        /* if($this->checkLimitHoursPlane($request->flight_airplane, $request->hours) && $request->flight_type == 'vuelo'){
            return response()->json(["errors" => ["No hay horas disponibles en el avión"]], 402);
        } */

        DB::statement('CALL storeAcademicFlight(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $request->id_student,             // id_student: INT
            $user->id,                        // id_employee: INT
            $request->id_instructor,          // id_instructor: INT
            $request->flight_type,            // flight_type: VARCHAR(50)
            $request->flight_date,            // flight_date: DATE
            $request->flight_hour,            // flight_hour: VARCHAR(10)
            $request->flight_payment_status,  // flight_payment_status: VARCHAR(50)
            $request->hours,                  // hours: INT
            floatval($request->total),        // total: DECIMAL(8, 2) -- Convertir a decimal
            $request->id_pay_method,          // pay_method: VARCHAR(50)
            $request->due_week,               // due_week: INT
            floatval($request->installment_value), // installment_value: DECIMAL(8, 2)
            $request->flight_category,        // flight_category: ENUM('VFR', 'IFR', 'IFR_nocturno')
            $request->maneuver,               // maneuver: ENUM('local', 'ruta')
            floatval($request->hour_instructor_cost), // hour_instructor_cost: DECIMAL(8, 2)
            $request->equipo,                 // equipo: ENUM('XBPDY', 'simulador', 'vuelo')
            $request->flight_session,         // session_id: INT
            $request->flight_airplane         // airplane_id: INT
        ]);

        $last_id_insert = flightHistory::latest('id')->first();
        $message = $request->flight_payment_status == 'pending' ? 'Vuelo agendado, pendiente de pago' : 'Se agendo el vuelo';

        $lastPaymentInsert = Payments::latest('id')->first();

        $PdfController = new PDFController();
        $urlTicket = $PdfController->generateTicket($last_id_insert->id, $request->payment_comission);

        $lastPaymentInsert->payment_ticket = $urlTicket;
        $lastPaymentInsert->save();

        return response()->json(["msg" => $message, "id" => $last_id_insert->id], 201);
    }

    /*
 *      payload : {
 *          id_instructor,
 *          flight_date,
 *          flight_hour,
 *          equipo,
 *          hours,
 *          flight_type,
 *          flight_category,
 *          maneuver,
 *          total,
 *          hour_instructor_cost,
 *          pay_method (efectivo),
 *      }
 * **/
    function storeAcademicFlight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_instructor' => 'required|numeric|exists:employees,id',
            'flight_date' => 'required|string',
            'flight_hour' => 'required|string',
            'equipo' => 'required|string|exists:info_flights,id',
            'hours' => 'required|numeric',
            'flight_type' => 'required|string|in:simulador,vuelo',
            'flight_category' => 'required|string|in:VFR,IFR,IFR_nocturno',
            'maneuver' => 'required|string|in:local,ruta',
            'total' => 'required|numeric',
            'hour_instructor_cost' => 'required|numeric',
            'pay_method' => 'required|string|in:efectivo',
        ], [
            'id_instructor.required' => 'campo requerido',
            'id_instructor.exists' => 'Selecciona un instructor',
            'flight_type.required' => 'campo requerido',
            'flight_type.in' => 'El tipo de vuelo no es válido',
            'flight_date.required' => 'campo requerido',
            'flight_date.date' => 'La fecha de vuelo no es válida',
            'flight_hour.required' => 'campo requerido',
            'flight_hour.string' => 'La hora de vuelo no es válida',
            'hours.required' => 'campo requerido',

        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        /* if ($this->OtherFlightReserved($request->flight_date, $request->flight_hour, $request->hours, $request->flight_type)) {
            return response()->json(["errors" => ["sameDate" => ["Existe un vuelo en la fecha y hora por favor de seleccionar otra hora"]]], 400);
        } */

        if ($this->isDateRestricted($request->flight_date, $request->flight_hour, $request->hours, $request->equipo)) {
            return response()->json(["errors" => ["La fecha seleccionada coincide con una fecha inhabil. Por favor, selecciona otra hora."]], 400);
        }
        $empleado = Employee::find($request->id_instructor);
        if ($empleado->user_type != 'instructor') {
            return response()->json(["errors" => ["El empleado no es un instructor"]], 400);
        }

        $student = Student::find($request->id_student);
        if ($request->pay_method == 'credit') {
            $hoursCredit = $this->getPriceFly($request->flight_type) * $request->hours;
            if ($student->flight_credit < $hoursCredit) {
                return response()->json(["errors" => ["El estudiante no tiene suficientes créditos"]], 400);
            }
        }
        DB::statement('CALL storeAcademicFlight(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $request->id_student,             // id_student: INT
            Auth::user()->id,                 // id_employee: INT
            $request->id_instructor,          // id_instructor: INT
            $request->flight_type,            // flight_type: VARCHAR(50)
            $request->flight_date,            // flight_date: DATE
            $request->flight_hour,            // flight_hour: VARCHAR(10)
            $request->flight_payment_status,  // flight_payment_status: VARCHAR(50)
            $request->hours,                  // hours: INT
            $request->total,                  // total: INT
            $request->pay_method,             // pay_method: VARCHAR(50)
            $request->due_week,               // due_week: INT
            $request->installment_value,      // installment_value: DECIMAL(8, 2)
            $request->flight_category,        // flight_category: ENUM('VFR', 'IFR', 'IFR_nocturno')
            $request->maneuver,               // maneuver: ENUM('local', 'ruta')
            $request->hour_instructor_cost,   // hour_instructor_cost: DECIMAL(8, 2)
            $request->equipo,                 // equipo: ENUM('XBPDY', 'simulador', 'vuelo')
            $request->flight_session,         // session_id: INT
            $request->flight_airplane         // airplane_id: INT
        ]);


        $message = $request->flight_payment_status == 'pending' ? 'Vuelo agendado, pendiente de pago' : 'Se agendo el vuelo';
        return response()->json(["msg" => $message], 201);
    }




    function getEmployeesByStudent(int $id)
    {
        $employees = DB::select("
            SELECT DISTINCT
                employees.id,
                employees.name AS instructor,
                employees.user_type
            FROM employees
            LEFT JOIN student_subjects ON employees.id = student_subjects.id_teacher
            LEFT JOIN students ON student_subjects.id_student = students.id
            WHERE employees.user_type = 'instructor' OR employees.user_type = 'flight_instructor';
        ");
        return response()->json($employees, 200);
    }

    function getPriceFly(string $name)
    {
        return InfoFlight::where('flight_type', $name)->value('price');
    }

    public function update(Request $request)
    {
        try {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'name' => 'required|string',
            'last_names' => 'required|string',
            'curp' => 'required|string|unique:students,curp,' . $request->student_id,
            'phone' => 'required|string',
            'cellphone' => 'required|string',
            'email' => 'required|email|unique:students,email,' . $request->student_id,
            'emergency_contact' => 'required|string',
            'emergency_phone' => 'required|string',
            'emergency_direction' => 'required|string',
        ], [
            'id_student.required' => 'El id del estudiante es requerido',
            'id_student.exists' => 'El id del estudiante no existe',
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre no es válido',
            'last_names.required' => 'El apellido es requerido',
            'last_names.string' => 'El apellido no es válido',
            'curp.required' => 'La CURP es requerida',
            'curp.string' => 'La CURP no es válida',
            'curp.unique' => 'La CURP ya está en uso',
            'phone.required' => 'El teléfono es requerido',
            'phone.string' => 'El teléfono no es válido',
            'cellphone.required' => 'El celular es requerido',
            'cellphone.string' => 'El celular no es válido',
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'El correo electrónico no es válido',
            'email.unique' => 'El correo electrónico ya está en uso',
            'emergency_contact.required' => 'El contacto de emergencia es requerido',
            'emergency_contact.string' => 'El contacto de emergencia no es válido',
            'emergency_phone.required' => 'El teléfono de emergencia es requerido',
            'emergency_phone.string' => 'El teléfono de emergencia no es válido',
            'emergency_direction.required' => 'La dirección de emergencia es requerida',
            'emergency_direction.string' => 'La dirección de emergencia no es válida',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

            DB::transaction(function () use ($request) {
                $student = Student::find($request->student_id);
                if (!$student) {
                    return response()->json(["error" => "Estudiante no encontrado"], 404);
                }

                $student->name = $request->name;
                $student->last_names = $request->last_names;
                $student->curp = $request->curp;
                $student->phone = $request->phone;
                $student->cellphone = $request->cellphone;
                $student->email = $request->email;
                $student->emergency_contact = $request->emergency_contact;
                $student->emergency_phone = $request->emergency_phone;
                $student->emergency_direction = $request->emergency_direction;
                $student->save();

                return response()->json(["message" => "Estudiante actualizado"], 200);
            });
        } catch (\Exception $e) {
            return response()->json(["error" => "Error al actualizar el estudiante: " . $e->getMessage()], 500);
        }
    }

    public function getStudentSubjects(Int $id)
    {
        try{
            $user = Auth::user();
            $employee = Employee::where('user_identification', $user->user_identification)->first();

            $student = Student::find($id);
            if (!$student) {
                return response()->json(["error" => "Estudiante no encontrado"], 404);
            }
            $student_subjects = DB::table('student_subjects')
                ->where('id_student', $id)
                ->join('subjects', 'student_subjects.id_subject', '=', 'subjects.id')
                ->join('employees', 'student_subjects.id_teacher', '=', 'employees.id')
                ->select(
                    'subjects.name as subject_name',
                    'subjects.id as subject_id',
                    DB::raw('CONCAT(employees.name, " ", employees.last_names) as teacher_full_name'),
                    'employees.id as teacher_id',
                    'employees.user_identification as teacher_identification',
                )
                ->get();
            $instructors = Employee::where('user_type', 'instructor')
                ->where('id_base', $employee->id_base)
                ->get(['id', 'name', 'last_names']);

            $turns = DB::table('turns')
                ->get(['id', 'name']);

            $subjects = DB::table('subjects')
                ->get(['id', 'name']);

            return response()->json(['student_subjects' => $student_subjects, 'instructors' => $instructors, 'turns' => $turns, 'subjects' => $subjects], 200);
        }catch(\Exception $e){
            return response()->json(["msg" => "Internal Server Error"], 500);
        }
    }

    public function addSubjectToStudent(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'id_student' => 'required|exists:students,id',
                'id_subject' => 'required|exists:subjects,id',
                'id_instructor' => 'required|exists:employees,id',
                'id_turn' => 'required|exists:turns,id',
            ], [
                'id_student.required' => 'El id del estudiante es requerido',
                'id_student.exists' => 'El id del estudiante no existe',
                'id_subject.required' => 'El id de la materia es requerido',
                'id_subject.exists' => 'El id de la materia no existe',
                'id_instructor.required' => 'El id del instructor es requerido',
                'id_instructor.exists' => 'El id del instructor no existe',
                'id_turn.required' => 'El id del turno es requerido',
                'id_turn.exists' => 'El id del turno no existe',
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $exist = DB::table('student_subjects')
                ->where('id_student', $request->id_student)
                ->where('id_subject', $request->id_subject)
                ->first();

            if ($exist) {
                return response()->json(["errors" => ["El estudiante ya tiene la materia asignada"]], 400);
            }

            $student_subject = new StudentSubject();
            $student_subject->id_student = $request->id_student;
            $student_subject->id_subject = $request->id_subject;
            $student_subject->id_teacher = $request->id_instructor;
            $student_subject->id_turn = $request->id_turn;
            $student_subject->save();

            return response()->json(["message" => "Materia agregada al estudiante"], 201);
        }catch(\Exception $e){
            return response()->json(["msg" => "Internal Server Error"], 500);
        }
    }

    public function deleteSubjectFromStudent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_student' => 'required|exists:students,id',
                'id_subject' => 'required|exists:subjects,id',
            ], [
                'id_student.required' => 'El id del estudiante es requerido',
                'id_student.exists' => 'El id del estudiante no existe',
                'id_subject.required' => 'El id de la materia es requerido',
                'id_subject.exists' => 'El id de la materia no existe',
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            DB::transaction(function () use ($request) {
                DB::table('student_subjects')
                    ->where('id_student', $request->id_student)
                    ->where('id_subject', $request->id_subject)
                    ->delete();
            });

            return response()->json(["message" => "Materia eliminada del estudiante"], 200);
        } catch (\Exception $e) {
            return response()->json(["error" => "Error al eliminar la materia del estudiante: " . $e->getMessage()], 500);
        }
    }

    public function changeInstructorFromStudentSubject(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'student_id' => 'required|exists:students,id',
                    'subject_id' => 'required|exists:subjects,id',
                    'instructor_id' => 'required|exists:employees,id',
                ],
                [
                    'id_student.required' => 'El id del estudiante es requerido',
                    'id_student.exists' => 'El id del estudiante no existe',
                    'id_subject.required' => 'El id de la materia es requerido',
                    'id_subject.exists' => 'El id de la materia no existe',
                    'id_instructor.required' => 'El id del instructor es requerido',
                    'id_instructor.exists' => 'El id del instructor no existe',
                ]
            );

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            DB::transaction(function () use ($request) {
                $studen_subject = StudentSubject::where('id_student', $request->student_id)
                    ->where('id_subject', $request->subject_id)
                    ->first();

                if (!$studen_subject) {
                    return response()->json(["errors" => ["El estudiante no tiene la materia asignada"]], 400);
                }

                $studen_subject->id_teacher = $request->instructor_id;
                $studen_subject->save();

                return response()->json([["message" => "Instructor actualizado", 'data' => $studen_subject, 'req' => $request->all()]], 200);
            });
        } catch (\Exception $e) {
            return response()->json(["error" => "Error al actualizar el instructor: " . $e->getMessage()], 500);
        }
    }


    function OtherFlightReserved($flight_date, $flight_hour, $hours, $flight_type)
    {
        $startTime = Carbon::createFromFormat('Y-m-d H:i', "$flight_date $flight_hour");
        $endTime = $startTime->copy()->addHours($hours);

        $start_time_str = $startTime->format('H:i:s');
        $end_time_str = $endTime->format('H:i:s');

        $query = DB::table('flight_history')
            ->leftJoin('flight_payments', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->where('flight_history.flight_date', $flight_date)
            ->where('flight_history.flight_status', 'proceso')
            ->where('flight_history.type_flight', $flight_type)
            ->where('flight_history.flight_client_status', 'aceptado')
            ->where(function ($q) use ($start_time_str, $end_time_str) {
                $q->whereBetween('flight_history.flight_hour', [$start_time_str, $end_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$start_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$end_time_str]);
            })
            ->get();

        return $query->isNotEmpty();
    }

    public function isDateRestricted($flight_date, $flight_hour, $hours, $equipo)
    {
        // Convertir la hora de vuelo a un formato de tiempo
        $start_time_str = $flight_hour; // Hora de inicio
        $end_time_str = \Carbon\Carbon::parse($flight_hour)->addHours($hours)->format('H:i'); // Hora de fin

        // Obtener el día de la semana para la fecha del vuelo
        $dayOfWeek = \Carbon\Carbon::parse($flight_date)->dayOfWeek; // Obtiene el número del día de la semana (0 = Domingo, 1 = Lunes, ...)

        // Realizar la consulta para verificar restricciones
        return DB::table('flight_hours_restrictions')
            ->join('restriction_days', 'flight_hours_restrictions.id', '=', 'restriction_days.id_flight_restriction')
            ->where('restriction_days.id_day', $dayOfWeek) // Aquí se verifica el día
            ->where('flight_hours_restrictions.id_flight', $equipo) // Asumiendo que $equipo es el id de vuelo
            ->where(function ($query) use ($start_time_str, $end_time_str) {
                $query->whereBetween('flight_hours_restrictions.start_hour', [$start_time_str, $end_time_str])
                      ->orWhereBetween('flight_hours_restrictions.end_hour', [$start_time_str, $end_time_str])
                      ->orWhere(function ($query) use ($start_time_str, $end_time_str) {
                          $query->where('flight_hours_restrictions.start_hour', '<=', $start_time_str)
                                ->where('flight_hours_restrictions.end_hour', '>=', $end_time_str);
                      });
            })
            ->exists(); // Retorna true o false
    }

    function indexStudentsReport()
    {
        $student = Student::select(
            'flight_history.id as id_flight',
            'students.id as id_student',
            'students.name',
            'students.last_names',
            'info_flights.equipo as equipo',
            'flight_history.type_flight',
            'flight_history.flight_category',
            'flight_history.flight_date',
            'flight_history.has_report',
        )
            ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')
            ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('info_flights', 'flight_history.id_equipo', '=', 'info_flights.id')
            ->groupBy('students.name','students.last_names', 'info_flights.equipo', 'flight_history.flight_category', 'flight_history.flight_date', 'flight_history.id', 'flight_history.id', 'flight_history.type_flight', 'flight_history.has_report','students.id' )
            ->orderBy('flight_history.created_at', 'desc')
            ->where('flight_history.type_flight', 'vuelo')
            ->where('flight_history.flight_client_status', 'aceptado')
            ->where(function($query) {
                $query->where('flight_history.flight_status', 'proceso')
                      ->orWhere('flight_history.flight_status', 'hecho');
            })
            ->get();

        return response()->json($student, 200);
    }


    public function getStudentMonthlyPayments(int $id)
    {
        try {
            $monthly_payments = DB::table('monthly_payments')
                ->where('id_student', $id)
                ->get(['id', 'payment_date', 'amount', 'status', 'concept']);

            return response()->json(['monthly_payments' => $monthly_payments], 200);
        }catch (\Exception $e){
            return response()->json(["msg" => "Internal Server Error"], 500);
        }
    }

    public function getStudentAndOwedMonthlyPayments(int $id)
    {
        $student = Student::with('owed_and_pending_payments')
            ->where('id', $id)
            ->select('id', 'user_identification', 'name', 'last_names')
            ->first();

        if (!$student) {
            return response()->json(["error" => "Estudiante no encontrado"], 404);
        }

        return response()->json($student, 200);
    }

    public function getStudentNameAndIdentification(int $id){
        $student = Student::select('id', 'name', 'last_names', 'user_identification')->where('id', $id)->first();

        if(!$student){
            return response()->json(["error" => "Estudiante no encontrado"], 404);
        }

        return response()->json($student, 200);
    }

    public function indexSyllabus(string $name = null)
    {
        $studentsSyllabus = Student::select(
            'students.id',
            'flight_history.id as id_flight',
            'students.user_identification',
            'students.name',
            'students.last_names',
            'students.cellphone',
            'flight_history.type_flight'
        )
        ->rightJoin('flight_payments', 'students.id', '=', 'flight_payments.id_student')
        ->rightJoin('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->where('students.name', 'like', "%$name%")
        ->orWhere('students.last_names', 'like', "%$name%")
        ->get();

        $groupedSyllabus = $studentsSyllabus->groupBy('id')->map(function ($studentGroup) {
            $student = $studentGroup->first();
            $syllabus = $studentGroup->pluck('type_flight', 'id_flight')->unique()->map(function ($type_flight, $id_flight) {
                return [
                    'id_flight' => $id_flight,
                    'type_flight' => $type_flight
                ];
            })->values()->toArray();

            return [
                'id' => $student->id,
                'user_identification' => $student->user_identification,
                'name' => $student->name,
                'last_names' => $student->last_names,
                'cellphone' => $student->cellphone,
                'syllabus' => $syllabus
            ];
        })->values()->toArray();

        return response()->json($groupedSyllabus, 200);
    }


    function checkLimitHoursPlane($id_airplane, $new_hours) {
        // Obtener el límite de horas del avión y la suma de horas actuales de vuelo
        $queryResult = DB::table('flight_history')
            ->join('air_planes', 'air_planes.id', '=', 'flight_history.id_airplane')
            ->select(
                'air_planes.limit_hours',
                DB::raw('SUM(flight_history.hours) as total_hours')
            )
            ->where('air_planes.id', $id_airplane)
            ->groupBy('air_planes.limit_hours')
            ->first();

        if ($queryResult) {
            $current_total_hours = $queryResult->total_hours;
            $limit_hours = $queryResult->limit_hours;

            if (($current_total_hours + $new_hours) > $limit_hours) {
                // Se excede el límite de horas
                return true;
            }
        }
        // No se excede el límite de horas
        return false;
    }

    public function studentInfo(){

        $user = Auth::user();
        $student = Student::where('user_identification', $user->user_identification)->first();

        if(!$student){
            return response()->json(["error" => "Estudiante no encontrado"], 404);
        }

        return response()->json($student, 200);
    }

    public function getStudentSubjectsAsStudent()
    {
        $user = Auth::user();
        $student = Student::where('user_identification', $user->user_identification)->first();

        $student_subjects = DB::table('student_subjects')
            ->where('id_student', $student->id)
            ->join('subjects', 'student_subjects.id_subject', '=', 'subjects.id')
            ->join('employees', 'student_subjects.id_teacher', '=', 'employees.id')
            ->select(
                'subjects.name as subject_name',
                'subjects.id as subject_id',
                DB::raw('CONCAT(employees.name, " ", employees.last_names) as teacher_full_name'),
                'employees.id as teacher_id',
                'student_subjects.final_grade',
                'student_subjects.status',
                'student_subjects.id as student_subject_id',
            )
            ->get();

        return response()->json(['student_subjects' => $student_subjects], 200);
    }

    public function getStudentIncomes(){
    try {
        $user = Auth::user();

        $student = Student::where('user_identification', $user->user_identification)->first();
        if (!$student) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        $incomes = DB::table('income_details')
            ->join('incomes', 'income_details.id', '=', 'incomes.income_details_id')
            ->select('incomes.concept',
                'incomes.quantity',
                'incomes.total',
                'incomes.id',
                'income_details.payment_method',
                'income_details.payment_date',
                'income_details.ticket_path'
            )
            ->where('income_details.student_id', $student->id)
            ->orderBy('income_details.payment_date', 'desc')
            ->get();

        $flightPayments = DB::table('flight_payments')
            ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->select(
                DB::raw("'Pago de Vuelo' as concept"),
                DB::raw("'1' as quantity"),
                'payments.amount as total',
                'payments.id',
                'payments.id_payment_method as payment_method',
                'payments.created_at as payment_date',
                'payments.payment_ticket as ticket_path'
            )
            ->where('flight_payments.id_student', $student->id)
            ->where('flight_payments.payment_status', 'pagado')
            ->orderBy('payments.created_at', 'desc')
            ->get();

        $orders = DB::table('orders')
            ->select('products.name as concept',
                'order_details.quantity as quantity',
                DB::raw('SUM(products.price * order_details.quantity) as total'),
                'orders.id',
                'orders.id_payment_method as payment_method',
                'orders.order_date as payment_date',
                DB::raw("'' as ticket_path")
            )
            ->join('order_details', 'orders.id', '=', 'order_details.id_order')
            ->join('products', 'order_details.id_product', '=', 'products.id')
            ->where('orders.id_client', $student->id)
            ->where('orders.payment_status', 'pagado')
            ->groupBy('orders.id', 'products.name', 'orders.id_payment_method', 'orders.order_date', 'order_details.quantity')
            ->orderBy('orders.order_date', 'desc')
            ->get();


        $allPayments = $incomes->concat($flightPayments)->concat($orders);

        return response()->json([
            'incomes' => $allPayments
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function deleteAccessUser($id){
        try{
            $student = Student::find($id);

            if(!$student){
                return response()->json(["error" => "Estudiante no encontrado"], 404);
            }

            $user = User::where('user_identification', $student->user_identification)->first();

            if(!$user){
                return response()->json(["errors" => ["El usuario de acceso no existe"]], 404);
            }

            DB::transaction(function() use ($user) {
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
                $user->delete();
            });

            return response()->json(["msg" => "Usuario eliminado"], 200);
        }catch (\Exception $e){
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }

    public function createAccessUser($id){
        try{
            $student = Student::find($id);

            if(!$student){
                return response()->json(["error" => "Estudiante no encontrado"], 404);
            }

            $user = User::where('user_identification', $student->user_identification)->first();

            if($user){
                return response()->json(["errors" => ["El usuario ya existe"]], 400);
            }

            $user = User::create([
                'user_identification' => $student->user_identification,
                'password' => bcrypt($student->curp),
                'user_type' => 'student',
                'id_base' => $student->id_base
            ]);

            return response()->json(["msg" => "Usuario creado"], 201);

        }catch(\Exception $e){
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }

    public function deleteDirectory($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function deleteStudent($id){
    try{
        $student = Student::find($id);

        if(!$student){
            return response()->json(["error" => "Estudiante no encontrado"], 404);
        }

        DB::transaction(function() use ($student) {
            $user = User::where('user_identification', $student->user_identification)->first();
            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

            $studentBase = Base::find($student->id_base);
            $baseName = strtolower($studentBase->name);
            $baseName = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $baseName);

            $folderPath = public_path("bases/{$baseName}/{$student->user_identification}");
            $this->deleteDirectory($folderPath);

            $user->delete();
            $student->delete();
        });

        return response()->json(["msg" => "Estudiante eliminado"], 200);
    }catch (\Exception $e){
        return response()->json(["error" => "Internal Server Error"], 500);
    }
}

    public function studentPendingPayments($id = null){
        try{

            if($id){
                $student = Student::find($id);
            }else{
                $user = Auth::user();
                $student = Student::where('user_identification', $user->user_identification)->first();
            }

            if (!$student) {
                return response()->json(['error' => 'Estudiante no encontrado'], 404);
            }

            $flightPayments = DB::table('flight_payments')
                ->where('id_student', $student->id)
                ->where('payment_status', 'pendiente')
                ->select('total as amount', 'payment_status as status', DB::raw("'No especificada' as payment_date"), DB::raw("'Flight Payment' as concept"))
                ->get();

            $monthlyPayments = DB::table('monthly_payments')
                ->where('id_student', $student->id)
                ->whereIn('status', ['pending', 'owed'])
                ->select('amount', 'status', 'payment_date', 'concept')
                ->get();

            $orders = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.id_order')
                ->join('products', 'order_details.id_product', '=', 'products.id')
                ->where('id_client', $student->id)
                ->where('payment_status', 'pendiente')
                ->select(DB::raw('SUM(products.price * order_details.quantity) as amount'), 'payment_status as status', 'order_date as payment_date', 'products.name as concept')
                ->groupBy('orders.id', 'products.name', 'order_date', 'payment_status')
                ->get();

            $pendingPayments = $flightPayments->concat($monthlyPayments)->concat($orders);

            return response()->json(['pending_payments' => $pendingPayments], 200);
        }catch(\Exception $e){
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }


    public function updateStudentPassword(){
        try {
            $user = Auth::user();
            $student = Student::where('user_identification', $user->user_identification)->first();

            if (!$student) {
                return response()->json(['error' => 'Estudiante no encontrado'], 404);
            }

            $validator = Validator::make(request()->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            ], [
                'current_password.required' => 'La contraseña actual es requerida',
                'current_password.string' => 'La contraseña actual no es válida',
                'new_password.required' => 'La nueva contraseña es requerida',
                'new_password.string' => 'La nueva contraseña no es válida',
                'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
                'confirm_password.required' => 'La confirmación de la contraseña es requerida',
                'confirm_password.same' => 'Las contraseñas no coinciden',
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            if (!Hash::check(request('current_password'), $user->password)) {
                return response()->json(['errors' => ['password' => ['La contraseña actual no es correcta']]], 400);
            }

            $user->password = bcrypt(request('new_password'));
            $user->save();

            DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

            return response()->json(['msg' => 'Contraseña actualizada'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

}
