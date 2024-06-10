<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Pending;
use App\Models\Student;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class AnalyticController extends Controller
{
    /*
    - numero de pentiendes en estado uncompleted
    - cantidad de alumnos
    - cantidad de instructores
 */
    function getCardData()
    {
        $totalStudents = Student::where('user_type', 'student')->count();
        $totalInstructors = Student::where('user_type', 'instructor')->count();
        $totalPendingsToday = Pending::where('status', 'uncompleted')
            ->where('date_to_complete', Date::now()->toDateString())
            ->count();

        return response()->json([
            'students' => $totalStudents,
            'instructors' => $totalInstructors,
            'pendings' => $totalPendingsToday
        ]);
    }

    function getEnrollmentsYear()
    {
        $enrollmentsByMonth = Enrollment::select(DB::raw('MONTH(date) as month'), DB::raw('COUNT(*) as enrollments'))
            ->groupBy(DB::raw('MONTH(date)'))
            ->get();

        $formattedResponse = [];
        foreach ($enrollmentsByMonth as $enrollment) {
            $formattedResponse[] = [
                'month' => $enrollment->month,
                'enrollments' => $enrollment->enrollments
            ];
        }
        return response()->json($formattedResponse);
    }

    function getWeekActivity()
    {
    }
}
