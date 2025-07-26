<?php

namespace App\Http\Controllers;

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

        return $flights;
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
}
