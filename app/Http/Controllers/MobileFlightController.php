<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\flightHistory;
use App\Models\NewSletter;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileFlightController extends Controller
{

    /*
    * NOTE: Medoto para ver los vuelos proximos del estudiante
    * */
    function FlightNearby($student)
    {
        $flights = flightHistory::select(
                'students.user_identification',
                'students.id as id_student',
                'students.name',
                'students.last_names',
                'flight_history.type_flight',
                'flight_history.hours',
                'flight_history.flight_date',
                'flight_history.flight_hour'
            )
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->where('flight_payments.id_student', $student->id)
            ->whereBetween('flight_history.flight_date', [
                Carbon::now()->startOfDay(),                // Inicio de hoy
                Carbon::now()->addDays(5)->endOfDay()       // Fin dentro de 5 días
            ])
            ->get();

        if(!$flights){
            return response()->json([
                "message" => "No se encontraron vuelos prontos",
                "success" => false,
                "data" => []
            ]);
        }

        return response()->json([
            "message" => "Registros obtenidos",
            "success" => true,
            "data" => $flights
        ]);
    }


    public function newSlettersIndex()
    {
        $user = Auth::user();
        $user_type = $user->user_type;
        $userController = new UserController();
        $base = $userController->getBaseAuth($user);

        // Query to get newsletters with the necessary joins
        $newSlettersQuery = NewSletter::select(
                'new_sletters.id as id_newsletter',
                'new_sletters.id_base',
                'new_sletters.is_active',
                'new_sletters.created_by as created_by_id',
                'employees.name as created_by',
                'new_sletters.title',
                'new_sletters.content',
                'new_sletters.direct_to',
                'new_sletters.file',
                'new_sletters.start_at as start_date',
                'new_sletters.expired_at as expired_date',
                'new_sletters.created_at as created_date',
            )
            ->leftJoin('employees', 'employees.id', '=', 'new_sletters.created_by')
            ->OrderBy('new_sletters.created_at', 'desc');

        // Apply filters based on user type
        if ($user_type !== 'root') {
            switch ($user_type) {
                case 'student':
                    $newSlettersQuery->where('new_sletters.direct_to', 'estudiantes')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'employee':
                    $newSlettersQuery
                        ->where('new_sletters.direct_to', 'empleados')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'instructor':
                    $newSlettersQuery->where('new_sletters.direct_to', 'instructores')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'flight_instructor':
                    $newSlettersQuery->where('new_sletters.direct_to', 'flight_instructor')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
            }
        }

        // Apply base filter
        $newSlettersQuery->whereExists(function ($query) use ($base) {
            $query->select(DB::raw(1))
                  ->from('employees as e')
                  ->whereRaw('e.id = new_sletters.created_by')
                  ->where('e.id_base', $base->id);
        });

        $newSletters = $newSlettersQuery->get();

       $client_id = $userController->getClientId($user->id);


        // is ownser of the newsletter
        $newSletters = $newSletters->map(function ($newsletter) use ($client_id) {
            $newsletter->is_owner = $newsletter->created_by_id == $client_id;
            return $newsletter;
        });

        // Transform is_active from 0/1 to false/true
        $newSletters = $newSletters->map(function ($newsletter) {
            $newsletter->is_active = $newsletter->is_active == 1;
            return $newsletter;
        });

        return $newSletters;
    }


    function pilotHomeScreenMobile(){
        $user = Auth::user();
        $student = Student::where('user_identification', $user->user_identification)->first();
        $newSletters = $this->newSlettersIndex();
        $flightNearby = $this->FlightNearby($student);
        $responseData = [
            'newsletters' => $newSletters ?? [],
            'flights_nearby' => $flightNearby ?? []
        ];


        return response()->json([
            'success' => true,
            'data' => $responseData,
        ]);

    }

    function getReserveData(){
        $sessions = [
            [
                'label' => 'Horario 1: 7:30 - 9:30',
                'hours' => 2,
                'start' => '7:30',
                'end' => '9:30',
                'hour' => '7:30',
            ],
            [
                'label' => 'Horario 2: 9:30 - 11:30',
                'hours' => 2,
                'start' => '9:30',
                'end' => '11:30',
                'hour' => '9:30',
            ],
            [
                'label' => 'Horario 3: 11:30 - 13:30',
                'hours' => 2,
                'start' => '11:30',
                'end' => '13:30',
                'hour' => '11:30',
            ],
            [
                'label' => 'Horario 4: 13:30 - 15:30',
                'hours' => 2,
                'start' => '13:30',
                'end' => '15:30',
                'hour' => '13:30',
            ],
            [
                'label' => 'Horario 5: 15:30 - 17:30',
                'hours' => 2,
                'start' => '15:30',
                'end' => '17:30',
                'hour' => '15:30',
            ],
            [
                'label' => 'Horario de ruta: 60 horas',
                'hours' => 6,
                'start' => '7:30',
                'end' => '13:30',
                'hour' => '7:30',
            ],
        ];

        $category = ['VFR', 'IFR', 'IFR_NOCTURNO'];
        $airplanes = ['XBPDY', 'Comanche'];

        $response = [
            'sessions' => $sessions,
            'category' => $category,
            'airplanes' => $airplanes
        ];

        return response()->json($response);
    }

public function instructorNearbyFlights(){
    $flights = flightHistory::select(
            'students.user_identification',
            'students.id as id_student',
            'students.name',
            'students.last_names',
            'flight_history.type_flight',
            'flight_history.hours',
            'flight_history.flight_date',
            'flight_history.flight_hour',
            'flight_history.id as id_flight',
            'air_planes.model',
            'flight_history.flight_status',
            DB::raw("DATE_FORMAT(DATE_ADD(STR_TO_DATE(flight_history.flight_hour, '%H:%i'), INTERVAL flight_history.hours HOUR), '%H:%i') as end_hour")
        )
        ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->join('students', 'students.id', '=', 'flight_payments.id_student')
        ->join('air_planes', 'air_planes.id', '=', 'flight_history.id_airplane')
        ->whereBetween('flight_history.flight_date', [
            Carbon::now()->startOfDay(),                // Inicio de hoy
            Carbon::now()->addDays(5)->endOfDay()       // Fin dentro de 5 días
        ])
        ->get();

    return $flights;
}
}
