<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\flightHistory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticController extends Controller
{
    /*
    - numero de reportes con estado sin reporte
    - cantidad de alumnos
    - Desgaste de aerolinea
     */
    function getCardData()
    {
        $totalStudents = User::where('user_type', 'student')->count();

        $airline_hours = AirPlane::select('tacometer')->first();
        $analiticTotalHour = AirPlane::select('limit_hours')->first();
        $totalHours = $airline_hours ? $airline_hours->tacometer : 0;

        $totalReportsPending = FlightHistory::where('has_report', 0)->count();

        return response()->json([
            'students' => $totalStudents,
            'airline_hours' => $totalHours,
            'airline_total_hours' => $analiticTotalHour ? $analiticTotalHour->limit_hours : 0,
            'pendings' => $totalReportsPending
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


function getTotalDebt() {
    $students = DB::select("SELECT
    students.id,
    students.name,
    students.last_names,
    students.cellphone,
    COALESCE(inscription.total_inscription_debt, 0) AS total_inscription_debt,
    COALESCE(flight.total_flight_debt, 0) AS total_flight_debt,
    COALESCE(inscription.total_inscription_debt, 0) + COALESCE(flight.total_flight_debt, 0) AS total_debt
FROM students
LEFT JOIN (
    SELECT
        id_student,
        SUM(amount) AS total_inscription_debt
    FROM monthly_payments
    WHERE status = 'pending' AND payment_date = CURDATE() OR payment_date < CURDATE()
    GROUP BY id_student
) AS inscription ON inscription.id_student = students.id
LEFT JOIN (
    SELECT
        id_student,
        SUM(total) AS total_flight_debt
    FROM flight_payments
    WHERE payment_status = 'pendiente' GROUP BY id_student
) AS flight ON flight.id_student = students.id;");

    return response()->json($students);
}

}
