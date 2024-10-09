<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\InfoFlight;
use App\Models\Option;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InfoFlightController extends Controller
{

    public function index()
    {
        return InfoFlight::all();
    }

    public function getFlightPrice(){
        $flightAmount = InfoFlight::where('equipo', 'XBPDY')->value('price');
        return $flightAmount;
    }

    public function getSimulatorFlightPrice(){
        $flightAmount = InfoFlight::where('equipo', 'simulador')->value('price');
        return $flightAmount;
    }

    function getEquipFlight()
    {
        $equipoValues = flightHistory::getEnumValues('equipo');
        return response()->json($equipoValues);
    }

    function getFlightType()
    {
        $flightTypeValues = flightHistory::getEnumValues('type_flight');
        return response()->json($flightTypeValues);
    }

    function getFlightCategory()
    {
        $flightCategoryValues = flightHistory::getEnumValues('flight_category');
        return response()->json($flightCategoryValues);
    }

    function getFlightManeuver()
    {
        $maneuverValues = flightHistory::getEnumValues('maneuver');
        return response()->json($maneuverValues);
    }

    function studentsFlightHistory($student_name = null)
    {
        $reportQuery = FlightPayment::select(
            'students.id as student_id',
            'students.user_identification as student_identification',
            'students.name as student_name',
            'students.last_names as student_last_names',
            DB::raw('SUM(payments.amount) as total_amount'),
            DB::raw('COUNT(DISTINCT CASE WHEN flight_history.type_flight = "vuelo" THEN flight_history.id ELSE NULL END) as total_flights'),
            DB::raw('COUNT(DISTINCT CASE WHEN flight_history.type_flight = "simulador" THEN flight_history.id ELSE NULL END) as total_simulators')
        )
        ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
        ->join('students', 'students.id', '=', 'flight_payments.id_student')
        ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
        ->where(function($query) use ($student_name) {
            $query->where('students.name', 'like', '%' . $student_name . '%')
                  ->orWhere('students.last_names', 'like', '%' . $student_name . '%');
        })
        ->where('flight_history.flight_client_status', 'aceptado')
        ->groupBy('students.id', 'students.user_identification', 'students.name', 'students.last_names')
        ->get();

        return response()->json($reportQuery);
    }


    /*
     * Función que retorna el historial de vuelos de un estudiante
     * @Return [
     *      student_id,
     *      student_name,
     *      student_identification,
     *      flights: [
     *          {
     *              "id_flight": number
     *              "id_flight": number,
     *              "flight_type": string,
     *              "flight_category": string,
     *              "flight_date": string,
     *              "flight_hour": string,
     *              "flight_hours": string,
     *              "session_name": string,
     +              "total": string
     *          }
     *      ]
     * ]
     *
     * */

function flightHistory($id_student) {
    $results = DB::table('flight_payments')
        ->select(
            'students.id as student_id',
            'flight_history.id as id_flight',
            'students.name as student_name',
            'students.user_identification as student_identification',
            'employees.name as instructor_name',
            'flight_history.type_flight as flight_type',
            'flight_history.flight_category as flight_category',
            'flight_history.maneuver',
            'flight_history.flight_date as flight_date',
            'flight_history.flight_hour as flight_hour',
            'flight_history.hours as flight_hours',
            'sessions.name as session_name',
            'flight_payments.total as total',
            'flight_history.flight_status as flight_status',
            'payment_methods.type as payment_method'
        )
        ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
        ->join('payment_methods', 'payments.id_payment_method', '=', 'payment_methods.id')
        ->join('students', 'flight_payments.id_student', '=', 'students.id')
        ->join('employees', 'flight_payments.id_instructor', '=', 'employees.id')
        ->leftJoin('sessions', 'flight_history.id_session', '=', 'sessions.id')
        // Modifica y revisa los LEFT JOIN según corresponda
        ->where('students.id', $id_student)
        ->where('flight_history.flight_client_status', 'aceptado')
        ->distinct() // Evita duplicados
        ->groupBy(
            'students.id',
            'flight_history.id',
            'employees.name',
            'flight_history.type_flight',
            'flight_history.flight_category',
            'flight_history.flight_date',
            'flight_history.flight_hour',
            'flight_history.hours',
            'sessions.name',
            'flight_payments.total',
            'flight_history.flight_status',
            'flight_history.maneuver',
            'payment_methods.type',
            'students.user_identification',
            'students.name'
        )
        ->orderBy('flight_history.created_at', 'desc')
        ->get();

    $result = [];
    foreach ($results as $data) {
        if (!isset($result[$data->student_id])) {
            $result[$data->student_id] = [
                'student_id' => $data->student_id,
                'student_name' => $data->student_name,
                'student_identification' => $data->student_identification,
                'flights' => []
            ];
        }

        // Evitar duplicados manualmente (opcional)
        $exists = false;
        foreach ($result[$data->student_id]['flights'] as $flight) {
            if ($flight['id_flight'] == $data->id_flight) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $result[$data->student_id]['flights'][] = [
                'instructor_name' => $data->instructor_name,
                'payment_method' => $data->payment_method,
                'id_flight' => $data->id_flight,
                'id_student' => $data->student_id,
                'flight_type' => $data->flight_type,
                'flight_category' => $data->flight_category,
                'flight_manuever' => $data->maneuver,
                'flight_date' => $data->flight_date,
                'flight_hour' => $data->flight_hour,
                'flight_hours' => $data->flight_hours,
                'session_name' => $data->session_name,
                'flight_status' => $data->flight_status,
                'total' => $data->total
            ];
        }
    }

    return response()->json(array_values($result));
}


function flightRequestIndex() {
    $canReservate = Option::where('option_type', 'can_reservate_flight')->pluck('is_active')->first();

    $results = DB::table('flight_payments')
        ->select(
            'students.id as student_id',
            'flight_history.id as id_flight',
            'students.name as student_name',
            'students.user_identification as student_identification',
            'employees.name as instructor_name',
            'flight_history.type_flight as flight_type',
            'flight_history.flight_category as flight_category',
            'flight_history.maneuver',
            'flight_history.flight_date as flight_date',
            'flight_history.flight_hour as flight_hour',
            'flight_history.hours as flight_hours',
            'sessions.name as session_name',
            'flight_payments.total as total',
            'flight_history.flight_status as flight_status',
            'payment_methods.type as payment_method',
            'flight_history.flight_client_status',
            DB::raw("'{$canReservate}' as can_reservate")
        )
        ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
        ->join('payment_methods', 'payments.id_payment_method', '=', 'payment_methods.id')
        ->join('students', 'flight_payments.id_student', '=', 'students.id')
        ->join('employees', 'flight_payments.id_instructor', '=', 'employees.id')
        ->leftJoin('sessions', 'flight_history.id_session', '=', 'sessions.id')
        ->leftJoin('lesson_objetive_sessions', 'lesson_objetive_sessions.id_session', '=', 'sessions.id')
        ->leftJoin('flight_objetives', 'flight_objetives.id', '=', 'lesson_objetive_sessions.id_flight_objetive')
        ->leftJoin('lessons', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
        ->leftJoin('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
        ->leftJoin('stages', 'stages.id', '=', 'stage_sessions.id_stage')
        ->where('flight_history.flight_client_status', 'pendiente')
        ->groupBy(
            'employees.name',
            'flight_history.id',
            'flight_history.type_flight',
            'flight_history.flight_category',
            'flight_history.flight_date',
            'flight_history.flight_hour',
            'flight_history.hours',
            'sessions.name',
            'flight_payments.total',
            'students.id',
            'students.user_identification',
            'students.name',
            'flight_history.flight_status',
            'flight_history.maneuver',
            'payment_methods.type',
            'flight_history.flight_client_status'
        )
        ->orderBy('flight_history.created_at', 'desc')
        ->get();

    if ($results->isEmpty()) {
        // Retornar un arreglo con un objeto que contenga can_reservate si no hay resultados
        return response()->json([['can_reservate' => (bool) $canReservate]]);
    }

    // Procesar resultados y agregar información de vuelos conflictivos
    foreach ($results as $result) {
        $conflictingFlights = $this->OtherFlightReservedRequest($result->flight_date, $result->flight_hour, $result->flight_hours, $result->flight_type);

        $result->same_time = $conflictingFlights->contains(function($flight) use ($result) {
            return $flight->id_flight != $result->id_flight;
        });

        $result->flight_conflight = $conflictingFlights->filter(function($flight) use ($result) {
            return $flight->id_flight != $result->id_flight;
        })->map(function($flight) {
            return ['id_flight' => $flight->id_flight];
        })->values()->toArray();
    }

    // Agregar can_reservate a cada resultado
    $results = $results->map(function($result) use ($canReservate) {
        $result->can_reservate = (bool) $canReservate;
        return $result;
    });

    return response()->json($results);
}

    function OtherFlightReservedRequest($flight_date, $flight_hour, $hours, $flight_type)
    {
        $startTime = Carbon::createFromFormat('Y-m-d H:i', "$flight_date $flight_hour");
        $endTime = $startTime->copy()->addHours($hours);

        $start_time_str = $startTime->format('H:i:s');
        $end_time_str = $endTime->format('H:i:s');

        $query = DB::table('flight_history')
            ->leftJoin('flight_payments', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->where('flight_history.flight_date', $flight_date)
            ->where('flight_history.flight_status', 'proceso')
            ->where('flight_history.flight_client_status', 'pendiente')
            ->where('flight_history.type_flight', $flight_type)
            ->where(function ($q) use ($start_time_str, $end_time_str) {
                $q->whereBetween('flight_history.flight_hour', [$start_time_str, $end_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$start_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$end_time_str]);
            })
            ->select('flight_history.id as id_flight')
            ->get();

        return $query;
    }





    function getFlightSyllabusData($id_flight) {
        $results = DB::table('flight_history')
            ->select(
                'stages.id as id_stage',
                'stages.name as stage_name',
                'sessions.id as id_session',
                'sessions.name as session_name'
            )
            ->join('sessions', 'sessions.id', '=', 'flight_history.id_session')
            ->join('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
            ->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
            ->where('flight_history.id', $id_flight)
            ->get();

        return response()->json($results);
    }


    /*
     *
     * Función que retorna los vuelos reservados en un rango de horas
     * @Return boolean
     * @Param flight_date: string
     * @Param flight_hour: string
     * @Param hours: number
     * @Param flight_type: string
     *
     * */

    function OtherFlightReserved($flight_date, $flight_hour, $hours, $id_equipo, $currentReservationId = null)
    {
        $startTime = Carbon::createFromFormat('Y-m-d H:i', "$flight_date $flight_hour");
        $endTime = $startTime->copy()->addHours($hours);

        $start_time_str = $startTime->format('H:i:s');
        $end_time_str = $endTime->format('H:i:s');

        // Buscar en flight_history
        $query = DB::table('flight_history')
            ->leftJoin('flight_payments', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->where('flight_history.flight_date', $flight_date)
            ->where('flight_history.flight_status', 'proceso')
            ->where('flight_history.id_equipo', $id_equipo)
            ->where(function ($q) use ($start_time_str, $end_time_str) {
                $q->whereBetween('flight_history.flight_hour', [$start_time_str, $end_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$start_time_str])
                    ->orWhereRaw('? BETWEEN flight_history.flight_hour AND ADDTIME(flight_history.flight_hour, SEC_TO_TIME(flight_history.hours * 3600))', [$end_time_str]);
            });

        if ($currentReservationId) {
            $query->where('flight_history.id', '!=', $currentReservationId);
        }

        $queryResult = $query->get();

        // Buscar en flight_customers
        $queryCustomer = DB::table('flight_customers')
            ->where('reservation_date', $flight_date)
            ->where('flight_status', 'pendiente')
            ->where('id_flight', $id_equipo)
            ->where(function ($q) use ($start_time_str, $end_time_str) {
                $q->whereBetween('reservation_hour', [$start_time_str, $end_time_str])
                    ->orWhereRaw('? BETWEEN reservation_hour AND ADDTIME(flight_customers.reservation_hour, SEC_TO_TIME(flight_customers.flight_hours * 3600))', [$start_time_str])
                    ->orWhereRaw('? BETWEEN reservation_hour AND ADDTIME(flight_customers.reservation_hour, SEC_TO_TIME(flight_customers.flight_hours * 3600))', [$end_time_str]);
            });

        if ($currentReservationId) {
            $queryCustomer->where('id', '!=', $currentReservationId);
        }

        $queryCustomerResult = $queryCustomer->get();

        return $queryResult->count() > 0 || $queryCustomerResult->count() > 0;
    }

    function getTypeFlightById(int $id_flight) {
        $query = InfoFlight::select('equipo')->where('id', $id_flight)->first();
        return $query->equipo;
    }

    /*
     *
     *
     * @Return :
     * [
     *      "flights" => [
     *          {
                  id: number;
                  equipo: string;
                  price: number;
                  created_at: string;
                  updated_at: string;

     *          }...
     *      ]
     *      "aiplanes" => [
     *          {
                  id: number
                  model: string
                  limit_hours: number
                  limit_weight: number
                  limit_passengers: number
                  tacometer: number
                }...
            ]
     * ]
     *
     **/

    public function AirplaneFlightIndex(){
        $flights = InfoFlight::all();
        $airplanes = AirPlane::all();

        return response()->json([
            'flights' => $flights,
            'airplanes' => $airplanes
        ], 200);
    }

}
