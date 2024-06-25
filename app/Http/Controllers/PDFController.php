<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    public function generateTicket($res)
    {
        $result = $res->toArray();
        $pdf = PDF::loadView('ticket', ['result' => $result]);
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="flightReservationTiket.pdf"');
    }

    public function getReservationTicket($flightHistoryId)
    {
        $result = DB::table('flight_payments')
            ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('employees', 'employees.id', '=', 'flight_payments.id_employee')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->join('payments', 'payments.id_flight', '=', 'flight_history.id')
            ->join('bases', 'bases.id', '=', 'employees.id_base')
            ->select(
                'employees.name as authorized_by',
                'students.user_identification as student_identification',
                'students.name as student_name',
                'flight_history.type_flight as item',
                'flight_history.hours as quantity',
                'flight_payments.total as item_total',
                'payments.payment_method as payment_method',
                DB::raw('flight_payments.total as subtotal'),
                DB::raw('flight_payments.total * 0.16 as iva'),
                DB::raw('SUM(flight_payments.total) * 1.16 as total'),
                'bases.location as location'
            )
            ->where('flight_history.id', $flightHistoryId)
            ->groupBy(
                'employees.name',
                'students.user_identification',
                'students.name',
                'flight_history.type_flight',
                'flight_history.hours',
                'flight_payments.total',
                'payments.payment_method',
                'flight_payments.payment_status',
                'bases.location'
            )
            ->get();

            if ($result->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'No data found for this flight history ID.']);
            }

            return $this->generateTicket($result);
    }
}
