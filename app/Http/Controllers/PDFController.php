<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{

    public function generateTicketInstallment($id_flight_history)
    {
        $response = $this->queryTicketInstallment($id_flight_history);

        if (!$response) {
            abort(404, 'Ticket not found');
        }

        $data = [
            'baseData' => (object)[
                'location' => $response->location,
            ],
            'employeeName' => $response->authorized_by,
            'employeeLastNames' => $response->authorized_by_last_names,
            'studentData' => (object)[
                'user_identification' => $response->student_identification,
                'name' => $response->student_name,
                'last_names' => $response->student_last_names,
            ],
            'incomeDetails' => (object)[
                'payment_method' => $response->payment_method,
                'commission' => '0.00',
                'total' => $response->total,
            ],
            'data' => [
                [
                    'quantity' => 1,
                    'concept' => 'Abono de cuota de vuelo',
                    'total' => $response->total,
                ]
            ],
        ];

        $pdf = PDF::loadView('income_ticket', $data);
        $student = Student::find($response->id_student);

        $fileController = new FileController();
        $url =  $fileController->saveTicket($pdf, $student, $response->id_base);
        return $url;
    }

    function queryTicketInstallment($id_flight_history){
        return DB::table('payments')
            ->join('flight_history', 'flight_history.id', '=', 'payments.id_flight')
            ->join('flight_payments', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->join('employees', 'employees.id', '=', 'flight_payments.id_instructor')
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.id_payment_method')
            ->join('bases', 'bases.id', '=', 'employees.id_base')
            ->select(
                'bases.location',
                'employees.name as authorized_by',
                'employees.last_names as authorized_by_last_names',
                'students.user_identification as student_identification',
                'students.name as student_name',
                'students.last_names as student_last_names',
                'payment_methods.type as payment_method',
                'payments.amount as total',
                'flight_history.id as flightHistoryId',
                'students.id as id_student',
                'bases.id as id_base'
            )
            ->where('flight_history.id', '=', $id_flight_history)
            ->orderBy('payments.created_at', 'desc')
            ->first(); // Devuelve el primer resultado directamente desde la función
    }




    public function generateTicket($id_flight_history)
    {
        $response = $this->getReservationTicket($id_flight_history);
        $apiData = $response->first();

        if (!$apiData) {
            abort(404, 'Ticket not found');
        }

        $data = [
            'baseData' => (object)[
                'location' => $apiData['location'],
            ],
            'employeeName' => $apiData['authorized_by'],
            'employeeLastNames' => $apiData['authorized_by_last_names'],
            'studentData' => (object)[
                'user_identification' => $apiData['student_identification'],
                'name' => $apiData['student_name'],
                'last_names' => $apiData['student_last_names'],
            ],
            'incomeDetails' => (object)[
                'payment_method' => $apiData['items']['payment_method'],
                'commission' => '0.00',
                'total' => $apiData['items']['total'],
            ],
            'data' => [
                [
                    'quantity' => $apiData['items']['quantity'],
                    'concept' => $apiData['items']['item'],
                    'total' => $apiData['items']['total'],
                ]
            ],
        ];

        $pdf = PDF::loadView('income_ticket', $data);
        $student = Student::find($apiData['id_student']);

        $fileController = new FileController();
        $url =  $fileController->saveTicket($pdf, $student, $apiData['id_base']);
        return $url;
    }

    public function getReservationTicket($flightHistoryId)
    {
        $results = DB::table('flight_payments')
            ->join('flight_history', 'flight_payments.id_flight', '=', 'flight_history.id')
            ->join('employees', 'employees.id', '=', 'flight_payments.id_employee')
            ->join('students', 'students.id', '=', 'flight_payments.id_student')
            ->join('payments', 'payments.id_flight', '=', 'flight_history.id')
            ->join('bases', 'bases.id', '=', 'employees.id_base')
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.id_payment_method')
            ->select(
                'students.id as id_student',
                'employees.id as id_employee',
                'students.id_base as id_base',
                'employees.name as authorized_by',
                'employees.last_names as authorized_by_last_names',
                'students.user_identification as student_identification',
                'students.name as student_name',
                'students.last_names as student_last_names',
                'flight_history.type_flight as item',
                'flight_history.hours as quantity',
                'payment_methods.type as payment_method',
                DB::raw('flight_payments.total as subtotal'),
                DB::raw('flight_payments.total * 0.16 as iva'),
                DB::raw('flight_payments.total'),
                'bases.location as location'
            )
            ->where('flight_history.id', $flightHistoryId)
            ->groupBy(
                'students.id',
                'employees.id',
                'students.id_base',
                'employees.name',
                'employees.last_names',
                'students.user_identification',
                'students.name',
                'students.last_names',
                'flight_history.type_flight',
                'flight_history.hours',
                'payment_methods.type',
                'bases.location',
                'flight_payments.total'
            )
            ->get();

        $response = $results->map(function ($result) {
            return [
                'id_student' => $result->id_student,
                'id_employee' => $result->id_employee,
                'id_base' => $result->id_base,
                'authorized_by' => $result->authorized_by,
                'authorized_by_last_names' => $result->authorized_by_last_names,
                'student_identification' => $result->student_identification,
                'student_name' => $result->student_name,
                'student_last_names' => $result->student_last_names,
                'subtotal' => number_format($result->subtotal, 2, '.', ''),
                'iva' => number_format($result->iva, 4, '.', ''),
                'location' => $result->location,
                'items' => [
                    'quantity' => number_format($result->quantity, 2, '.', ''),
                    'item' => $result->item,
                    'payment_method' => $result->payment_method,
                    'total' => number_format($result->total, 4, '.', ''),
                ],
            ];
        });

        return $response;
    }
}

