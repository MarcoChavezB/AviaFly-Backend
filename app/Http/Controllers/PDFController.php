<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    public function generateTiket($res)
    {
        $result = $res->toArray();
        $pdf = PDF::loadView('tiket', ['result' => $result]);
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="flightReservationTiket.pdf"');
    }

public function getReservationTiket($flightHistoryId)
{
    $result = DB::table('flight_payments')
        ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
        ->join('employees', 'employees.id', '=', 'flight_payments.id_employee')
        ->join('students', 'students.id', '=', 'flight_payments.id_student')
        ->select(
            'employees.name as authorized_by',
            'students.user_identification as student_identification',
            'students.name as student_name',
            'flight_history.type_flight as flight_type',
            'flight_history.hours as flight_hours',
            'flight_payments.total as flight_total',
            'flight_payments.payment_status as payment_status',
            DB::raw('flight_payments.total as subtotal'),
            DB::raw('flight_payments.total * 0.16 as iva'),
            DB::raw('SUM(flight_payments.total) * 1.16 as total')
        )
        ->where('flight_history.id', $flightHistoryId)
        ->groupBy(
            'employees.name',
            'students.user_identification',
            'students.name',
            'flight_history.type_flight',
            'flight_history.hours',
            'flight_payments.total',
            'flight_payments.payment_status'
        )
        ->get();

    if ($result->isEmpty()) {
        return redirect()->back()->withErrors(['error' => 'No data found for this flight history ID.']);
    }

    return $this->generateTiket($result);
}

}


/**
        // Obtener datos de la base de datos
        $tiket = FlightPayment::select(
            "flight_history.id",
            "flight_history.hours",
            "flight_history.flight_date",
            "flight_history.type_flight",
            "flight_payments.total",
            "flight_payments.payment_status",
            "payments.payment_method",
            "flight_payments.due_week",
            "flight_payments.created_at"
        )
        ->join("flight_history", "flight_history.id", "=", "flight_payments.id_flight")
        ->join("students", "students.id", "=", "flight_payments.id_student")
        ->join("payments", "payments.id_flight", "=", "flight_payments.id")
        ->orderBy("flight_payments.created_at", "desc")
        ->limit(1)
        ->get();

        $tiketArray = $tiket->toArray();

        foreach ($tiketArray as &$item) {
            $createdAt = Carbon::parse($item['created_at']);
            $dueWeek = (int) $item['due_week'];

            $item['payments'] = ($dueWeek > 0) ? collect(range(1, $dueWeek))->map(function ($week) use ($createdAt, $item, $dueWeek) {
                return [
                    'day' => $createdAt->copy()->addWeeks($week)->format('Y-m-d'),
                    'amount' => number_format($item['total'] / $dueWeek, 2)
                ];
            })->toArray() : [];
        }
        return response()->json($tiketArray);
*/
