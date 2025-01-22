<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\flightHistory;
use App\Models\MonthlyPayment;
use App\Models\OrderDetail;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
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

        $totalReportsPending = FlightHistory::where('has_report', 0)
            ->where('type_flight', 'vuelo')
            ->where('flight_client_status', 'aceptado')
            ->where('flight_history.flight_status', '!=', 'cancelado') // Condición para excluir el estado "cancelado"
            ->count();

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
    $flights = collect(flightHistory::select(
            'flight_history.id as order_id',
            'students.user_identification',
            'students.id',
            'students.name as student_name',
            'students.phone',
            'flight_history.flight_date as order_date',
            'flight_payments.total as order_total',
            'flight_history.type_flight as concept'
        )
        ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->join('students', 'students.id', '=', 'flight_payments.id_student')
        ->where('flight_payments.payment_status', 'pendiente')
        ->whereDate('flight_history.flight_date', Carbon::today())
        ->get());

    $monthlyPayments = collect(MonthlyPayment::select(
            'monthly_payments.id as order_id',
            'students.user_identification',
            'students.id',
            'students.name as student_name',
            'students.phone',
            'monthly_payments.payment_date as order_date',
            'monthly_payments.amount as order_total',
            'monthly_payments.concept as concept'
        )
        ->join('students', 'students.id', '=', 'monthly_payments.id_student')
        ->where('monthly_payments.status', 'pending')
        ->whereDate('monthly_payments.payment_date', Carbon::today())
        ->get());

    $orders = collect(OrderDetail::select(
            'orders.id as order_id',
            'students.user_identification',
            'students.id',
            'students.name as student_name',
            'students.phone',
            'orders.order_date as order_date',
            'orders.total as order_total',
            'products.name as concept'
        )
        ->join('orders', 'orders.id', '=', 'order_details.id_order')
        ->join('products', 'order_details.id_product', '=', 'products.id')
        ->leftJoin('students', 'students.id', '=', 'orders.id_client')
        ->where('orders.payment_status', 'pendiente')
        ->whereDate('orders.order_date', Carbon::today())
        ->get());

    // Aseguramos que todos los datos se fusionen correctamente
    $data = $flights->merge($monthlyPayments)->merge($orders);

    // Retornamos todos los registros como JSON
    return response()->json($data);
}

}
