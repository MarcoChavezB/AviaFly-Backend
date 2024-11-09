<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\FlightCustomer;
use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\InfoFlight;
use App\Models\Student;
use App\Models\Payments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\FlightStatusNotify;
use App\Mail\RequestFlightAccepted;
use App\Mail\RequestFlightDeclined;
use App\Models\Employee;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\InfoFlightController;
use App\Mail\RequestFlight;
use App\Models\FlightHoursRestrictions;
use stdClass;

class FlightHistoryController extends Controller
{

    public function changeStatusRequest(Request $request)
    {
        $userController = new UserController();
        $client = $userController->getIdEmploye(Auth::user()->user_identification);

        $flight = flightHistory::find($request->flightId);
        if (!$flight) {
            return response()->json(['error' => 'Vuelo no encontrado'], 404);
        }

        $flight->flight_client_status = $request->flightClientStatus;
        $flight->save();

        $flightPayment = FlightPayment::where("id_flight", $request->flightId)->first();
        if (!$flightPayment) {
            return response()->json(['error' => 'Pago de vuelo no encontrado'], 404);
        }

        $flightPayment->id_employee = $client;
        $flightPayment->save();

        $message = $request->comments;

        $student = Student::where('flight_payments.id_flight', $request->flightId)
            ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')->first();

        if (!$student) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        if ($request->flightClientStatus === 'aceptado') {
            if (is_array($request->flightConflicts)) {
                foreach ($request->flightConflicts as $conflict) {
                    $conflictFlight = flightHistory::find($conflict['id_flight']);
                    if ($conflictFlight) {
                        $conflictStudent = Student::where('flight_payments.id_flight', $conflict['id_flight'])
                            ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')->first();

                        if ($conflictStudent) {
                            $messageDeclined = "La solicitud de vuelo ha sido rechazada, ya que existe un conflicto con otra reservación agendada previamente. Por favor seleccione otra fecha para su vuelo.";
                           Mail::to($conflictStudent->email)->send(new RequestFlightDeclined($conflictStudent, $conflictFlight, $messageDeclined));

                            $conflictFlight->flight_client_status = 'rechazado';
                            $conflictFlight->save();

                            $conflictFlightPayment = FlightPayment::where("id_flight", $conflict['id_flight'])->first();
                            if ($conflictFlightPayment) {
                                $conflictFlightPayment->id_employee = $client;
                                $conflictFlightPayment->save();
                            }
                        }
                    }
                }
            }
            Mail::to($student->email)->send(new RequestFlightAccepted($student, $flight, $message));
        } else if ($request->flightClientStatus === 'rechazado') {
            // Devolver los creditos del estudiante

            $flightTypeReservation = $flight->type_flight;

            switch($flightTypeReservation){
                case 'vuelo':
                    $student->flight_credit = (float)$student->flight_credit + $request->hours;
                break;
                case 'simulador':
                    $student->simulator_credit = (float)$student->simulator_credit + $request->hours;
                break;
            }


            $student->save();
            Mail::to($student->email)->send(new RequestFlightDeclined($student, $flight, $message));
        }

        return response()->json(['msg' => 'Solicitud actualizada', "student" => $student], 200);
    }


public function indexReport(int $id_flight)
{
    $report = Student::select(
        'flight_history.id as id_flight',
        'flight_history.flight_status',
        'flight_payments.payment_status',
        'students.id as id_student',
        'students.name',
        'students.last_names',
        'flight_history.type_flight',
        'flight_payments.total',
        'flight_history.initial_horometer',
        'flight_history.final_horometer',
        'flight_history.total_horometer',
        'flight_history.final_tacometer',
        'flight_history.comment',
        'flight_history.flight_date',
        'payments.payment_ticket'
    )
    ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')
    ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
    ->leftjoin('payments', 'payments.id_flight', '=', 'flight_history.id')
    ->where('flight_history.id', $id_flight)
    ->where('flight_history.flight_client_status', 'aceptado')
    ->orderBy('flight_history.created_at', 'desc')
    ->first();  // Obtiene el primer registro

    if ($report) {
        $reportArray = $report->toArray();  // Convierte el modelo a un arreglo
    } else {
        $reportArray = [];  // Retorna un arreglo vacío si no hay resultados
    }

    return response()->json([$reportArray], 200);  // Devuelve el arreglo como un JSON
}

    function flightsData(int $id_student, int $flightHistory = null)
    {
        $query = FlightPayment::select(
            'flight_payments.id as id_flight',
            'students.id as id_student',
            'students.curp',
            'flight_history.type_flight as tipo_vuelo',
            'flight_history.flight_date as fecha_vuelo',
            'flight_history.flight_hour as hora_vuelo',
            'flight_payments.payment_status as status_pago',
            DB::raw('flight_history.flight_status as status_vuelo'),
            'flight_payments.total as total_dinero',
            DB::raw('COALESCE(SUM(payments.amount), 0) as total_amounts'),
            DB::raw('flight_payments.total - COALESCE(SUM(payments.amount), 0) as deuda_viva'),
            'payments.id_flight',
        )
            ->leftJoin('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->leftJoin('students', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->where('students.id', $id_student);

        if (!is_null($flightHistory)) {
            $query->where('flight_history.id', $flightHistory);
        }

        $flights = $query->groupBy(
            'students.curp',
            'students.id',
            'flight_history.type_flight',
            'flight_history.flight_date',
            'flight_history.flight_hour',
            'flight_history.flight_status',
            'flight_payments.total',
            'payments.id_flight',
            'flight_payments.payment_status',
            'flight_payments.id'
        )
            ->OrderBy('flight_history.created_at', 'desc')
            ->get();

        $data = $flights->map(function ($flights) {
            $history_amounts = DB::table('payments')
                ->select('payments.amount', 'payments.created_at', 'payment_methods.type as payment_method', 'payments.payment_voucher')
                ->join('payment_methods', 'payment_methods.id', '=', 'payments.id_payment_method')
                ->where('id_flight', $flights->id_flight)
                ->get();
            $lastItem = $history_amounts->last();

            return [
                'id_flight' => $flights->id_flight,
                'id_student' => $flights->id_student,
                'payment_method' => $lastItem ? $lastItem->payment_method : null,
                'curp' => $flights->curp,
                'flight_type' => $flights->tipo_vuelo,
                'flight_date' => $flights->fecha_vuelo,
                'hour_flight' => $flights->hora_vuelo,
                'flight_status' => $flights->status_vuelo,
                'payment_status' => $flights->status_pago,
                'total' => $flights->total_dinero,
                'total_amounts' => $flights->total_amounts,
                'debt' => $flights->deuda_viva,
                'history_amounts' => $history_amounts
            ];
        });

        return response()->json($data, 200);
    }

    function reportDataById(int $id_flight)
    {
        $flights = FlightPayment::select(
            'flight_payments.id as id_flight',
            'students.curp',
            'flight_history.flight_alone',
            'flight_history.initial_horometer',
            'flight_history.final_horometer',
            'flight_history.total_horometer',
            'flight_history.final_tacometer',
            'flight_history.comment',
            'flight_history.hours',
            'info_flights.equipo as equipo',
            'students.name',
            'students.last_names',
            'students.flight_credit',
            'flight_history.type_flight as tipo_vuelo',
            'flight_history.flight_date as fecha_vuelo',
            'flight_history.flight_hour as hora_vuelo',
            'flight_payments.payment_status as status_pago',
            DB::raw('flight_history.flight_status as status_vuelo'),
            'flight_payments.total as total_dinero',
            DB::raw('COALESCE(SUM(payments.amount), 0) as total_amounts'),
            DB::raw('flight_payments.total - COALESCE(SUM(payments.amount), 0) as deuda_viva'),
            'payments.id_flight'
        )
            ->leftJoin('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->leftJoin('students', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->leftJoin('info_flights', 'info_flights.id', '=', 'flight_history.id_equipo')
            ->where('flight_payments.id', $id_flight)
            ->groupBy(
                'students.curp',
                'flight_history.hours',
                'flight_history.type_flight',
                'flight_history.flight_date',
                'flight_history.flight_hour',
                'flight_history.flight_status',
                'flight_payments.total',
                'payments.id_flight',
                'flight_payments.payment_status',
                'flight_payments.id',
                'students.name',
                'students.last_names',
                'students.flight_credit',
                'flight_history.flight_alone',
                'flight_history.initial_horometer',
                'info_flights.equipo',
                'flight_history.final_horometer',
                'flight_history.total_horometer',
                'flight_history.final_tacometer',
                'flight_history.comment',
            )
            ->get();
        $data = $flights->map(function ($flights) {
            $history_amounts = DB::table('payments')
                ->select('amount', 'payment_method', 'created_at')
                ->where('id_flight', $flights->id_flight)
                ->get();

            return [
                'id_flight' => $flights->id_flight,
                'name' => $flights->name,
                'last_names' => $flights->last_names,
                'flight_credit' => $flights->flight_credit,
                'curp' => $flights->curp,
                'flight_type' => $flights->tipo_vuelo,
                'flight_date' => $flights->fecha_vuelo,
                'hour_flight' => $flights->hora_vuelo,
                'flight_hours' => $flights->hours,
                'equipo' => $flights->equipo,
                'flight_status' => $flights->status_vuelo,
                'payment_status' => $flights->status_pago,
                'total' => $flights->total_dinero,
                'total_amounts' => $flights->total_amounts,
                'debt' => $flights->deuda_viva,
                'flight_alone' => $flights->flight_alone,
                'initial_horometer' => $flights->initial_horometer,
                'final_horometer' => $flights->final_horometer,
                'total_horometer' => $flights->total_horometer,
                'final_tacometer' => $flights->final_tacometer,
                'comment' => $flights->comment,

                'history_amounts' => $history_amounts,
            ];
        });

        return response()->json($data, 200);
    }

public function changeStatusFlight(Request $request)
{
    $data = $request->all();
    $totalCreditFlight = 0;
    $penalty = $data['penaltyAmount'];
    $infoPayment = new PaymentMethodController();
    $infoFlightController = new InfoFlightController();

    $validator = Validator::make($data, [
        'id_flight' => 'required|integer',
        'status' => 'required|string',
    ]);
    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $flight = flightHistory::find($data['id_flight']);
    if (!$flight) {
        return response()->json(['msg' => 'Vuelo no encontrado'], 404);
    }

    if ($flight->flight_status == $data['status']) {
        return response()->json(['msg' => 'El vuelo ya está en el estado solicitado'], 400);
    }

    $flightPayment = FlightPayment::where('id_flight', $data['id_flight'])->first();
    $payments = Payments::where('id_flight', $data['id_flight'])->get();
    $student = Student::join('flight_payments', 'students.id', '=', 'flight_payments.id_student')
        ->where('flight_payments.id_flight', $data['id_flight'])
        ->first();
    $studentPerson = Student::find($student->id_student);

    // quitar el credito devuelto
    if ($flight->flight_status == 'cancelado') {
        if ($payments) {
            foreach ($payments as $item) {
                if ($item->id_payment_method == $infoPayment->getCreditoVueloId()) {
                    $totalCreditFlight += (float)$item->amount;
                }
            }
        }

        // convertir el total de monto a horas de credito
        if ($flight->type_flight == "vuelo") {
            $totalCreditFlight = $totalCreditFlight / $infoFlightController->getFlightPrice();
        } elseif ($flight->type_flight == "simulador") {
            $totalCreditFlight = $totalCreditFlight / $infoFlightController->getSimulatorFlightPrice();
        }

        if($studentPerson->flight_credit < $totalCreditFlight){
            return response()->json(['msg' => "El alumno no tiene suficientes creditos para restaurar el estado"], 400);
        }
        $studentPerson->flight_credit = (float)$studentPerson->flight_credit - $totalCreditFlight;
        $studentPerson->save();
    }

    $flight->flight_status = $data['status'];
    if ($data['status'] == 'cancelado') {
        $fileController = new FileController();
        $flightPayment->payment_status = "cancelado";

        $instructor = Employee::join('flight_payments', 'employees.id', '=', 'flight_payments.id_instructor')
            ->where('flight_payments.id_flight', $data['id_flight'])
            ->first();

        if ($payments) {
            // sumar el credito de vuelo gastado (simulador / vuelo)
            foreach ($payments as $item) {
                if ($item->id_payment_method == $infoPayment->getCreditoVueloId()) {
                    $totalCreditFlight += (float)$item->amount;
                }
            }
        }

        // multar al estudiante
        if($penalty != 0){
            if($flight->type_flight == "vuelo"){
                $totalPenaltyConvert = $penalty / $infoFlightController->getFlightPrice();
                $studentPerson->flight_credit = (float)$studentPerson->flight_credit - $totalPenaltyConvert;
            }
            if($flight->type_flight == "simulador"){
                $totalPenaltyConvert = $penalty / $infoFlightController->getSimulatorFlightPrice();
                $studentPerson->flight_credit = (float)$studentPerson->simulator_credit - $totalPenaltyConvert;
            }
        }else{
            // convertir el total en horas de vuelo
            if ($flight->type_flight == "vuelo") {
                $totalFlightHoursConvert = $totalCreditFlight / $infoFlightController->getFlightPrice();
                $studentPerson->flight_credit = (float)$studentPerson->flight_credit + $totalFlightHoursConvert;
            } elseif ($flight->type_flight == "simulador") {
                $totalSimulatorConvert = $totalCreditFlight / $infoFlightController->getSimulatorFlightPrice();
                $studentPerson->simulator_credit = (float)$studentPerson->simulator_credit + $totalSimulatorConvert;
            }
        }

        $studentPerson->save();
        $this->resetFlightData($flight->id);

        $details = new stdClass();
        $details->motive = $data['motive'] ?? null;
        $details->details = $data['details'] ?? null;

        if ($student) {
            Mail::to($student->email)->send(new FlightStatusNotify($student, $flight, $instructor, $data['status'], $details, $penalty));
        }
        if ($instructor) {
            Mail::to($instructor->email)->send(new FlightStatusNotify($student, $flight, $instructor, $data['status'], $details, $penalty));
        }
    }

    $flightPayment->save(); // Guarda el estado del pago del vuelo
    $flight->save(); // Guarda el estado del vuelo

    return response()->json([
        'msg' => 'El vuelo se ha modificado correctamente',
    ], 200);
}

function resetFlightData($id_flight)
{
    $flight = flightHistory::find($id_flight);
    if ($flight) { // Verifica si el vuelo existe
        $flight->flight_alone = 0;
        $flight->has_report = 0;
        $flight->initial_horometer = 0;
        $flight->final_horometer = 0;
        $flight->total_horometer = 0;
        $flight->final_tacometer = 0;
        $flight->save(); // Guarda los cambios en el vuelo
    }
}


    /*
 *  Payload:
 *
    {
    "horometroInicial": 1,
    "horometroFinal": 4.5,
    "tacometro": "100",
    "comments": "ksoakosk",
    "flight_alone": true,
    "total_horometro": 3.5
    }

    */

    function storeReport(Request $request)
    {
        $data = $request->all();

        // Validar los datos recibidos
        $validator = Validator::make($data, [
            'id_flight' => 'required|numeric',
            'horometroInicial' => 'required|numeric',
            'horometroFinal' => 'required|numeric',
            'flight_alone' => 'required|boolean',
            'total_horometro' => 'required|numeric',
            'tacometro' => 'nullable|numeric', // Añadido
        ], [
            'horometroInicial.required' => 'Campo requerido',
            'horometroFinal.required' => 'Campo requerido',
            'horometroInicial.numeric' => 'Dato incorrecto',
            'horometroFinal.numeric' => 'Dato incorrecto',
            'flight_alone.required' => 'Campo requerido',
            'total_horometro.required' => 'Campo requerido',
            'tacometro.numeric' => 'El tacómetro debe ser un número válido', // Mensaje personalizado
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Obtener el vuelo y el avión
        $flight = flightHistory::find($data['id_flight']);

        if (!$flight) {
            return response()->json(['msg' => 'Vuelo no encontrado'], 404);
        }

        $airplane = AirPlane::find($flight->id_airplane);
        if (!$airplane) {
            return response()->json(['msg' => 'Avión no encontrado'], 404);
        }

        $flight->flight_alone = $data['flight_alone'];
        $flight->initial_horometer = $data['horometroInicial'];
        $flight->final_horometer = $data['horometroFinal'];
        $flight->total_horometer = $data['total_horometro'];
        $flight->final_tacometer = isset($data['tacometro']) && is_numeric($data['tacometro'])
            ? floatval($data['tacometro'])
            : 0;
        $flight->comment = $data['comments'];
        $flight->has_report = 1;

        $flight->save();

        // Obtener la diferencia entre tacometros
        $tacometer_difference = DB::select("
            WITH RankedFlights AS (
                SELECT
                    final_tacometer,
                    ROW_NUMBER() OVER (ORDER BY id DESC) AS row_num
                FROM
                    flight_history
                WHERE
                    type_flight = 'vuelo'
            )
            SELECT
                MAX(CASE WHEN row_num = 1 THEN final_tacometer ELSE NULL END) -
                MAX(CASE WHEN row_num = 2 THEN final_tacometer ELSE NULL END) AS tacometer_difference
            FROM
                RankedFlights;
        ");

        $actual_tacometer = $airplane->tacometer;
        $difference = !empty($tacometer_difference) && isset($tacometer_difference[0]->tacometer_difference)
            ? $tacometer_difference[0]->tacometer_difference
            : 0;
        $airplane->tacometer = $actual_tacometer + $difference;

        // quitando funcion de diferencia de tacometro
/*      $airplane->save(); */

        return response()->json([
            'msg' => "El reporte se ha guardado correctamente"
        ]);
    }

    /**
        title: flight_type
        start:fligt_dateTflight_hour
        end: fligt_dateTflight_hour + flight_hours
     */
public function getFlightReservations()
{
    // Obtener la opción de reserva
    $canReservate = Option::select('option_type', 'is_active')
        ->where('option_type', 'can_reservate_flight')
        ->first();

    // Obtener los registros de FlightHistory
    $flightHistories = FlightHistory::select('flight_status', 'id', 'type_flight', 'flight_date', 'flight_hour', 'hours')
        ->where('flight_client_status', 'aceptado')
        ->groupBy('flight_status', 'type_flight', 'flight_date', 'flight_hour', 'hours', 'id')
        ->orderBy('flight_date', 'desc')
        ->limit(100)
        ->get();

    $flightHistories = $flightHistories->isEmpty() ? new Collection() : $flightHistories->map(function ($flight) use ($canReservate) {
        $start = Carbon::createFromFormat('Y-m-d H:i', $flight->flight_date . ' ' . $flight->flight_hour);
        $end = $start->copy()->addHours($flight->hours);

        return [
            'id' => $flight->id,
            'flight_status' => $flight->flight_status,
            'title' => $flight->type_flight,
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
            'source' => 'flight_history',
            'can_reservate' => $canReservate->is_active
        ];
    });

    // Obtener los registros de FlightCustomer
    $flightCustomers = FlightCustomer::select('payment_status as flight_status', 'id', 'flight_type as type_flight', 'reservation_date as flight_date', 'reservation_hour as flight_hour', 'flight_hours as hours')
        ->groupBy('payment_status', 'flight_type', 'reservation_date', 'reservation_hour', 'flight_hours', 'id')
        ->get();

    $flightCustomers = $flightCustomers->isEmpty() ? new Collection() : $flightCustomers->map(function ($flight) use ($canReservate) {
        $start = Carbon::createFromFormat('Y-m-d H:i', $flight->flight_date . ' ' . $flight->flight_hour);
        $end = $start->copy()->addHours($flight->hours);

        return [
            'id' => $flight->id,
            'flight_status' => $flight->flight_status,
            'title' => $flight->type_flight,
            'start' => $start->toIso8601String(),
            'end' => $end->toIso8601String(),
            'source' => 'flight_customers',
            'can_reservate' => $canReservate->is_active
        ];
    });

    // Obtener las restricciones de FlightHoursRestrictions
    $restrictions = FlightHoursRestrictions::with(['flight'])->get();

    $calendarRestrictions = [];

    foreach ($restrictions as $restriction) {
        $start_date = Carbon::parse($restriction->start_date);
        $end_date = $restriction->end_date ? Carbon::parse($restriction->end_date) : null;

        if ($end_date) {
            $currentDate = $start_date->copy();
            while ($currentDate <= $end_date) {
                $calendarRestrictions[] = [
                    'id' => $restriction->id,
                    'flight_status' => 'restriction',
                    'title' => $restriction->motive,
                    'start' => $currentDate->toDateString() . 'T00:00',
                    'end' => $currentDate->toDateString() . 'T23:59',
                    'source' => 'restriction',
                    'can_reservate' => null,
                ];
                $currentDate->addDay();
            }
        } else {
            $calendarRestrictions[] = [
                'id' => $restriction->id,
                'flight_status' => 'restriction',
                'title' => $restriction->motive,
                'start' => $start_date->toDateString() . 'T00:00',
                'end' => $start_date->toDateString() . 'T23:59',
                'source' => 'restriction',
                'can_reservate' => null,
            ];
        }
    }

    // Combinar todas las colecciones en una sola
    $flights = $flightHistories->merge($flightCustomers)->merge($calendarRestrictions);

    // Retornar la respuesta
    if ($flights->isEmpty()) {
        return response()->json([['can_reservate' => $canReservate->is_active]]);
    }

    return response()->json($flights);
}

    /**
        filtros para el reporte de vuelos
        payload:
        {
            "flight_date": "2021-09-01",
            "flight_end_date": "2021-09-30",
            "flight_type": "(simulador, vuelo)",
            "student_name": "jose"
        }
     */
    function indexStudentsFilter(Request $request)
    {
        $query = Student::select(
            'flight_history.id as id_flight',
            'students.id as id_student',
            'students.name',
            'students.last_names',
            'info_flights.equipo as equipo',
            'flight_history.type_flight',
            'flight_history.flight_category',
            'flight_history.flight_date',
            'flight_history.total_horometer',
            'flight_history.has_report',
        )
            ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')
            ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('info_flights', 'info_flights.id', '=', 'flight_history.id_equipo');

        $query->when($request->filled('student_name'), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $studentName = $request->input('student_name');
                $query->where('students.name', 'like', '%' . $studentName . '%')
                    ->orWhere('students.last_names', 'like', '%' . $studentName . '%');
            });
        });

        $query->when($request->filled('report_status'), function ($query) use ($request) {
            if($request->input('report_status') == '1'){
                $query->where('flight_history.has_report', 1);
            }else if($request->input('report_status') == '0'){
                $query->where('flight_history.has_report', 0);
            }
        });


        $query->when($request->filled('flight_type'), function ($query) use ($request) {
            $query->where('flight_history.type_flight', $request->input('flight_type'));
        });

        $query->when($request->filled(['flight_date', 'flight_end_date']), function ($query) use ($request) {
            $query->whereBetween('flight_history.flight_date', [$request->input('flight_date'), $request->input('flight_end_date')]);
        });

        $student = $query
            ->groupBy('students.name', 'flight_history.total_horometer', 'students.last_names', 'info_flights.equipo', 'flight_history.flight_category', 'flight_history.flight_date', 'flight_history.id', 'flight_history.id', 'students.id' , 'flight_history.type_flight', 'flight_history.has_report')
            ->get();

        return response()->json($student, 200);
    }
    /** */
    function getFlightDetails(string $id_flight)
    {
        $flight = flightHistory::select(
            'flight_history.id',
            'flight_history.hours',
            'flight_history.flight_status',
            'flight_history.type_flight',
            'flight_history.flight_date',
            'flight_history.flight_hour',
            'flight_payments.total',
            'flight_payments.payment_status',
            'employees.name as instructor',
            'employees.last_names as instructor_last_name',
            'employees.phone as instructor_phone',
            'students.id as id_student',
            'students.name as student_name',
            'students.last_names as student_last_name',
            'students.phone as student_phone'
        )
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->join('employees', 'employees.id', '=', 'flight_payments.id_instructor')
            ->where('flight_history.id', $id_flight)
            ->first();

        $payments = DB::table('payments')
            ->select('payments.payment_ticket', 'payment_methods.type as payment_method')
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.id_payment_method')
            ->where('payments.id_flight', $id_flight)
            ->get();

        if ($flight) {
            $flight->payments = $payments;
        }

        return response()->json([$flight], 200);
    }


    function getFLightReservationsById(int $id_student)
    {
        $flights = FlightHistory::select('flight_history.flight_status', 'flight_history.id', 'flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours')
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('flight_payments.id_student', $id_student)
            ->groupBy('flight_history.flight_status', 'flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours', 'flight_history.id')
            ->get();

        $flights = $flights->map(function ($flight) {
            $start = Carbon::createFromFormat('Y-m-d H:i', $flight->flight_date . ' ' . $flight->flight_hour);

            $end = $start->copy()->addHours($flight->hours);

            return [
                'id' => $flight->id,
                'flight_status' => $flight->flight_status,
                'title' => $flight->type_flight,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ];
        });

        return response()->json($flights);
    }


    function getFLightTypes(string $fligth_type)
    {

        if ($fligth_type != 'simulador' && $fligth_type != 'vuelo') {
            return response()->json([
                'msg' => 'Tipo de vuelo no válido'
            ], 400);
        }

        $flights = FlightHistory::select('flight_history.flight_status', 'flight_history.id', 'flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours')
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->where('flight_history.type_flight', $fligth_type)
            ->where('flight_client_status', 'aceptado')
            ->groupBy('flight_history.flight_status', 'flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours', 'flight_history.id')
            ->get();

        $flights = $flights->map(function ($flight) {
            $start = Carbon::createFromFormat('Y-m-d H:i', $flight->flight_date . ' ' . $flight->flight_hour);

            $end = $start->copy()->addHours($flight->hours);

            return [
                'id' => $flight->id,
                'flight_status' => $flight->flight_status,
                'title' => $flight->type_flight,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ];
        });

        return response()->json($flights);
    }

function getAllInfoReport(int $id_flight)
{
    // Fetch the second most recent final_horometer value for 'vuelo' flights
    $secondLatestFinalHorometer = DB::table('flight_history')
        ->where('type_flight', 'vuelo')
        ->orderBy('id', 'desc')
        ->skip(1) // Skip the most recent record to get the second most recent
        ->value('final_horometer');

    $flightReport = FlightPayment::select([
        'flight_history.flight_date',
        'flight_history.flight_hour',
        'info_flights.id as id_equipo',
        'info_flights.equipo',
        'sessions.name as session_title',
        'flight_history.type_flight',
        'instructor.name as instructor_name',
        'employee.name as employee_name',
        'flight_history.maneuver',
        'flight_history.flight_category',
        'flight_history.initial_horometer',
        'flight_history.final_horometer',
        'flight_history.final_tacometer',
        'flight_payments.hour_instructor_cost',
        'flight_history.comment',
        'flight_history.total_horometer',
        'flight_history.flight_alone',
        'flight_payments.total',
        'flight_history.hours',
        DB::raw('flight_payments.hour_instructor_cost * flight_history.hours AS total_payment_instructor'),
        'info_flights.price as hour_flight_price',
    ])
    ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
    ->leftJoin('employees as instructor', 'flight_payments.id_instructor', '=', 'instructor.id')
    ->leftJoin('employees as employee', 'flight_payments.id_employee', '=', 'employee.id')
    ->leftJoin('info_flights', 'flight_history.id_equipo', '=', 'info_flights.id')
    ->leftJoin('sessions', 'sessions.id', '=', 'flight_history.id_session')
    ->leftJoin('air_planes', 'air_planes.id', '=', 'flight_history.id_airplane')
    ->where('flight_history.id', $id_flight)
    ->orderBy('flight_history.flight_date', 'desc')
    ->orderBy('flight_history.flight_hour', 'desc')
    ->limit(1)
    ->get();

    // Adding the second most recent final_horometer as initial_horometer to each flightReport record
    $flightReport->each(function ($report) use ($secondLatestFinalHorometer) {
        if ($secondLatestFinalHorometer !== null) {
            $report->initial_horometer = $secondLatestFinalHorometer;
        }
    });

    return response()->json($flightReport, 200);
}

    function flightCreditStudent(string $name = null){

        $infoFlightController = new InfoFlightController();

        $flight_hour_cost = $infoFlightController->getFlightPrice();
        $simulator_hour_cost = $infoFlightController->getSimulatorFlightPrice();

        if (!$flight_hour_cost || !$simulator_hour_cost) {
            return response()->json(['error' => 'Cost data not found'], 404);
        }

        $students = Student::select(
                'students.id',
                'students.user_identification',
                'students.name',
                'students.last_names',
                'students.flight_credit',
                'students.simulator_credit',
                'students.credit',
                'students.cellphone'
            )
            ->where(function($query) use ($name) {
                $query->where('students.name', 'like', '%' . $name . '%')
                      ->orWhere('students.last_names', 'like', '%' . $name . '%');
            })
            ->get();

        $students->each(function ($student) use ($flight_hour_cost, $simulator_hour_cost) {
            $flight_credit_total = (float) $student->flight_credit * $flight_hour_cost;
            $simulator_credit_total = (float) $student->simulator_credit * $simulator_hour_cost;
            $student->flight_credit = $flight_credit_total;
            $student->simulator_credit = $simulator_credit_total;
            $student->total_credit = $flight_credit_total + $simulator_credit_total + (float) $student->credit;
        });

        return response()->json($students, 200);
    }


    function getSchedule(){
        $flightHistoryData = FlightHistory::select(
                'flight_history.id',
                'flight_history.created_at',
                'students.name as student_name',
                'employees.name as instructor_name',
                'flight_history.initial_horometer as hr_inicial',
                'flight_history.final_horometer as hr_final',
                'flight_history.final_tacometer as tacometro_total'
            )
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->join('employees', 'employees.id', '=', 'flight_payments.id_instructor')
            ->orderByDesc('flight_history.id')
            ->get();

        // Now calculate additional fields in PHP
        $prevTacometro = null;
        $prevHorometro = null;

        foreach ($flightHistoryData as $flight) {
            $flight->horometro_total = $flight->hr_final - $flight->hr_inicial;

            if ($prevTacometro === null) {
                $flight->prev_tacometro_total = 0;
            } else {
                $flight->prev_tacometro_total = $prevTacometro - $flight->tacometro_total;
            }

            if ($prevHorometro === null) {
                $flight->prev_horometro_total = 0;
            } else {
                $flight->prev_horometro_total = $prevHorometro - $flight->horometro_total;
            }

            $prevTacometro = $flight->tacometro_total;
            $prevHorometro = $flight->horometro_total;

            $flight->diferencia = $flight->prev_tacometro_total;
        }
        return response()->json($flightHistoryData, 200);
    }


    function requestFlightReservation( Request $request){

        $can = Option::select('is_active')->where('option_type', 'can_reservate_flight')->first();
        if(!$can){
            return response()->json(['error' => 'Option not found']);
        }

        $validator = Validator::make($request->all(), [
            'id_instructor' => 'required|numeric|exists:employees,id',
            'flight_date' => 'required|string',
            'flight_hour' => 'required|string',
            'equipo' => 'required|exists:info_flights,id',
            'hours' => 'required|numeric',
            'flight_type' => 'required|string|in:simulador,vuelo',
            'flight_category' => 'required|string|in:VFR,IFR,IFR_nocturno',
            'maneuver' => 'required|string|in:local,ruta',
            'total' => 'required|numeric',
            'hour_instructor_cost' => 'required|numeric',
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

        $reservationDateTime = Carbon::parse($request->flight_date . ' ' . $request->flight_hour);

        $minReservationDateTime = Carbon::now()->addDay();

        if ($reservationDateTime->lessThanOrEqualTo($minReservationDateTime)) {
            return response()->json(['errors' => 'Se necesita agendar con al menos 24 horas de anticipación'], 400);
        }

        $payment_method_controller = new PaymentMethodController();

        $id_pay_method = $payment_method_controller->getCreditoVueloId();

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        if ($this->OtherFlightReserved($request->flight_date, $request->flight_hour, $request->hours, $request->flight_type)) {
            return response()->json(["errors" => ["sameDate" => ["Existe un vuelo en la fecha y hora por favor de seleccionar otra hora"]]], 400);
        }

        $empleado = Employee::find($request->id_instructor);
        if ($empleado->user_type != 'flight_instructor') {
            return response()->json(["errors" => ["El empleado no es un instructor"]], 400);
        }

        $student = Student::find($request->id_student);
        $payment_method_controller = new PaymentMethodController();
        if ($id_pay_method == $payment_method_controller->getCreditoVueloId()) {
            $hoursCredit = $this->getPriceFly($request->flight_type) * $request->hours;
            if ($student->flight_credit < $hoursCredit) {
                return response()->json(["errors" => ["El estudiante no tiene suficientes créditos"]], 400);
            }
        }

        if($this->checkLimitHoursPlane($request->flight_airplane, $request->hours) && $request->flight_type == 'vuelo'){
            return response()->json(["errors" => ["No hay horas disponibles en el avión"]], 402);
        }

        // Eliminar la horas del estudiante
        $flightTypeReservation = $request->flight_type;

        switch($flightTypeReservation){
            case 'vuelo':
                $student->flight_credit = (float)$student->flight_credit - $request->hours;
            break;
            case 'simulador':
                $student->simulator_credit = (float)$student->simulator_credit - $request->hours;
            break;
        }

        $student->save();



        $flight = new flightHistory();
        $flight->hours = $request->hours;
        $flight->reservation_type = 'academico';
        $flight->flight_status = 'proceso';
        $flight->flight_client_status = 'aceptado';
        $flight->maneuver = $request->maneuver;
        $flight->flight_category = $request->flight_category;
        $flight->flight_date = $request->flight_date;
        $flight->flight_hour = $request->flight_hour;
        $flight->type_flight = $request->flight_type;
        $flight->id_equipo = $request->equipo;
        $flight->id_airplane = $request->flight_airplane;

        if($request->flight_session != 0){
            $flight->id_session = $request->flight_session;
        }

        $flight->initial_horometer = 0;
        $flight->final_horometer = 0;
        $flight->total_horometer = 0;
        $flight->final_tacometer = 0;
        $flight->save();


        $flightPayment = new FlightPayment();
        $flightPayment->id_student = $request->id_student;
        $flightPayment->id_flight = $flight->id;
        $flightPayment->id_instructor = $request->id_instructor;
        $flightPayment->id_employee = $request->id_employee;
        $flightPayment->total = $request->total;
        $flightPayment->payment_status = 'pagado';
        $flightPayment->hour_instructor_cost = $request->hour_instructor_cost;
        $flightPayment->due_week = $request->due_week;
        $flightPayment->save();

        $payment = new Payments();
        $payment->amount = $request->total;
        $payment->id_payment_method = $id_pay_method;
        $payment->id_flight = $flightPayment->id;
        $payment->save();

        $employee = Employee::select('email')
            ->join('users', 'users.user_identification', '=', 'employees.user_identification')
            ->where(function($query) {
                $query->where('users.user_type', 'admin')
                      ->orWhere('users.user_type', 'root')
                      ->orWhere('users.user_type', 'employee');
            })
            ->get();


        foreach ($employee as $emp) {
            Mail::to($emp)->send(new RequestFlight($student, $flight));
        }

        return response()->json(["msg" => "Peticion de vuelo registrada", 'employees' => $employee], 200);
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
            ->where('flight_history.flight_client_status', 'aceptado')
            ->where('flight_history.type_flight', $flight_type)
            ->where(function ($q) use ($start_time_str, $end_time_str) {
                $q->whereBetween('flight_history.flight_hour', [$start_time_str, $end_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$start_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$end_time_str]);
            })
            ->get();

        return $query->isNotEmpty();
    }



    function getPriceFly(string $id_equipo)
    {
        return InfoFlight::find($id_equipo);
    }
}
