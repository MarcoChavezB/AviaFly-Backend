<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\InfoFlight;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InfoFlightController extends Controller
{
    public function index()
    {
        return InfoFlight::all();
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

    function studentsFlightHistory($student_name = null){
        $reportQuery = FlightPayment::select(
            'students.id as student_id',
            'students.user_identification as student_identification',
            'students.name as student_name',
            'students.last_names as student_last_names',
            DB::raw('SUM(payments.amount) as total_amount'),
            DB::raw('COUNT(CASE WHEN flight_history.type_flight = "vuelo" THEN 1 ELSE NULL END) as total_flights'),
            DB::raw('COUNT(CASE WHEN flight_history.type_flight = "simulador" THEN 1 ELSE NULL END) as total_simulators'),
        )
        ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
        ->join('students', 'students.id', '=', 'flight_payments.id_student')
        ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
        ->where('students.name' , 'like', '%'.$student_name.'%')
        ->orWhere('students.last_names' , 'like', '%'.$student_name.'%')
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
                'flight_history.flight_date as flight_date',
                'flight_history.flight_hour as flight_hour',
                'flight_history.hours as flight_hours',
                'sessions.name as session_name',
                'flight_payments.total as total',
                'flight_history.flight_status as flight_status',
            )
            ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->join('students', 'flight_payments.id_student', '=', 'students.id')
            ->join('employees', 'flight_payments.id_instructor', '=', 'employees.id')
            ->leftJoin('sessions', 'flight_history.id_session', '=', 'sessions.id')
            ->leftJoin('lesson_objetive_sessions', 'lesson_objetive_sessions.id_session', '=', 'sessions.id')
            ->leftJoin('flight_objetives', 'flight_objetives.id', '=', 'lesson_objetive_sessions.id_flight_objetive')
            ->leftJoin('lessons', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
            ->leftJoin('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
            ->leftJoin('stages', 'stages.id', '=', 'stage_sessions.id_stage')
            ->where('students.id', $id_student)
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
                'flight_history.flight_status'
            )
            ->get();

        $result = [];
        foreach($results as $data) {
            if (!isset($result[$data->student_id])) {
                $result[$data->student_id] = [
                    'student_id' => $data->student_id,
                    'student_name' => $data->student_name,
                    'student_identification' => $data->student_identification,
                    'flights' => []
                ];
            }
            $result[$data->student_id]['flights'][] = [
                'instructor_name' => $data->instructor_name,
                'id_flight' => $data->id_flight,
                'flight_type' => $data->flight_type,
                'flight_category' => $data->flight_category,
                'flight_date' => $data->flight_date,
                'flight_hour' => $data->flight_hour,
                'flight_hours' => $data->flight_hours,
                'session_name' => $data->session_name,
                'flight_status' => $data->flight_status,
                'total' => $data->total
            ];
        }

        return response()->json(array_values($result));
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
}
