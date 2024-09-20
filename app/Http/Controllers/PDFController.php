<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Illuminate\Support\Facades\DB;
use stdClass;

class PDFController extends Controller
{

    public function generateTicketInstallment($id_flight_history)
    {
        $response = $this->queryTicketInstallment($id_flight_history);

        if (!$response) {
            abort(404, 'Ticket not found');
        }

        $comission = $response->total * ($response->commission / 100);
        $totalMoreComission = $response->total + $comission;

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
                'commission' => $comission . ' (' . $response->commission . '%)',
                'total' => $totalMoreComission,
            ],
            'data' => [
                [
                    'quantity' => 1,
                    'concept' => "Abono de " . $response->item,
                    'total' => $response->total ,
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
                'flight_history.type_flight as item',
                'students.id as id_student',
                'bases.id as id_base',
                'payment_methods.commission'
            )
            ->where('flight_history.id', '=', $id_flight_history)
            ->orderBy('payments.created_at', 'desc')
            ->first();
    }



    public function generateTicket($id_flight_history, $comission = 0)
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
                'commission' => $comission,
                'total' => $apiData['items']['total'],
            ],
            'data' => [
                [
                    'quantity' => $apiData['items']['quantity'],
                    'concept' => $apiData['items']['item'],
                    'total' => $apiData['items']['total'] - $comission,
                ]
            ],
        ];

        $pdf = PDF::loadView('income_ticket', $data);
        $student = Student::find($apiData['id_student']);

        $fileController = new FileController();
        $url =  $fileController->saveTicket($pdf, $student, $apiData['id_base']);
        return $url;
    }

    public function generateProductTicket($order_id){
        $response = $this->getProductOrderTicket($order_id);
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
                'payment_method' => $apiData['items'][0]['payment_method'],
                'commission' => $apiData['discount'],
                'total' => $apiData['price'],
            ],
            'data' => $apiData['items'],
        ];

        $pdf = PDF::loadView('products.shopProduct', $data);

        $student = new Student();
        $student->user_identification = 'ClientsAvia';

        if($apiData['id_student'] != 0){
            $student = Student::find($apiData['id_student']);
        }

        $fileController = new FileController();
        $url = $fileController->saveTicket($pdf, $student, $apiData['id_base']);
        return $url;
    }
    public function getProductOrderTicket($orderId)
    {
        $results = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.id_order')
            ->join('products', 'products.id', '=', 'order_details.id_product')
            ->leftJoin('students', 'students.id', '=', 'orders.id_client')
            ->join('employees', 'employees.id', '=', 'orders.id_employe')
            ->join('bases', 'bases.id', '=', 'employees.id_base')
            ->join('payment_methods', 'payment_methods.id', '=', 'orders.id_payment_method')
            ->join('product_payments', 'product_payments.id_order', '=', 'orders.id')
            ->leftJoin('discounts', 'discounts.id', '=', 'orders.id_discount')
            ->select(
                'orders.id as id_order',
                'discounts.discount',
                'students.id as id_student',
                'employees.id as id_employee',
                'bases.id as id_base',
                'employees.name as authorized_by',
                'employees.last_names as authorized_last_names',
                'students.user_identification as student_identification',
                'students.name as student_name',
                'students.last_names as student_last_names',
                DB::raw('orders.sub_total as subtotal'),
                DB::raw('orders.total * 0.16 as iva'),
                DB::raw('orders.total as total'),
                'bases.location as location',
                'order_details.quantity',
                'products.name as item',
                'products.price',
                'payment_methods.type as payment_method',
                'product_payments.amount as price'
            )
            ->where('orders.id', $orderId)
            ->get();

        $response = [];
        foreach ($results as $result) {
            if (!isset($response[$result->id_order])) {
                $response[$result->id_order] = [
                    'id_student' => $result->id_student ? $result->id_student : 0,
                    'id_employee' => $result->id_employee,
                    'id_base' => $result->id_base,
                    'authorized_by' => $result->authorized_by,
                    'authorized_by_last_names' => $result->authorized_last_names,
                    'student_identification' => $result->student_identification ? $result->student_identification : '0000000000',
                    'student_name' => $result->student_name ? $result->student_name : 'Cliente',
                    'student_last_names' => $result->student_last_names ? $result->student_last_names : 'Avia',
                    'subtotal' => number_format($result->subtotal, 2, '.', ''),
                    'iva' => number_format($result->iva, 2, '.', ''),
                    'location' => $result->location,
                    'price' => number_format($result->price, 2, '.', ''),
                    'discount' => $result->discount ? number_format($result->discount, 2, '.', '') : 0,
                    'items' => [], // Inicializamos un array para los productos
                    'total' => number_format($result->total, 2, '.', '')
                ];
            }

            $response[$result->id_order]['items'][] = [
                'quantity' => number_format($result->quantity, 2, '.', ''),
                'concept' => $result->item,
                'payment_method' => $result->payment_method,
                'total' => number_format($result->price, 2, '.', '') * $result->quantity,
            ];
        }

        return collect($response)->values();
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
                    'total' => number_format($result->total, 2, '.', ''),
                ],
            ];
        });

        return $response;
    }
}

