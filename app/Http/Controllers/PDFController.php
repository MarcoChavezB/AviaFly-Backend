<?php

namespace App\Http\Controllers;

use App\Models\FlightPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PDFController extends Controller
{
    function getReservationTiket()
    {
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
    }
}

