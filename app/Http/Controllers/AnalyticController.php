<?php
namespace App\Http\Controllers;

use App\Models\Pending;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class AnalyticController extends Controller
{
/*
    - numero de pentiendes en estado uncompleted
    - cantidad de alumnos
    - cantidad de instructores
 */
    function getCardData(){
        $totalStudents = User::where('user_type', 'student')->count();
        $totalInstructors = User::where('user_type', 'instructor')->count();
        $totalPendingsToday = Pending::where('status', 'uncompleted')
                                    ->where('date_to_complete', Date::now()->toDateString())
                                    ->count();

        return response()->json([
            'students' => $totalStudents,
            'instructors' => $totalInstructors,
            'pendings' => $totalPendingsToday
        ]);

    }

}
