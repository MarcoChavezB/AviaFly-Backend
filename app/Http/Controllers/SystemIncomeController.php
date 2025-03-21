<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemIncomeController extends Controller
{

    /*
     * Retorna todos los ingresos en el sistema
     *
     * - ordenes de productos,
     * - ingresos
     * - vuelos
     * - recreativos
     *
     * Table:
     *  {
     *      id
     *      student name
     *      income by
     *      income date
     *      payment method
     *      concepto
     *      descuento
     *      total
     *      ticket
     *      voucher
     *  }
     * */
    public function index()
    {

    // Orders
    $orders = DB::table('orders')
        ->leftJoin('product_payments', 'product_payments.id_order', '=', 'orders.id')
        ->leftJoin('order_details', 'orders.id', '=', 'order_details.id_order')
        ->leftJoin('products', 'order_details.id_product', '=', 'products.id')
        ->leftJoin('payment_methods', 'product_payments.id_payment_method', '=', 'payment_methods.id')
        ->leftJoin('students', 'orders.id_client', '=', 'students.id')
        ->leftJoin('employees', 'orders.id_employe', '=', 'employees.id')
        ->select(
            DB::raw("'type' as orders"),
            DB::raw("CASE WHEN students.user_identification IS NOT NULL THEN students.user_identification ELSE 'N/A' END as user_identification"),
            'orders.id',
            DB::raw("CASE WHEN students.id IS NOT NULL THEN students.name ELSE 'Cliente' END as student_name"),
            'employees.name as income_by',
            'orders.order_date as income_date',
            'payment_methods.type as payment_method',
            DB::raw("'Producto' as concept"),
            DB::raw("0.00 as discount"),
            'orders.total',
            'product_payments.payment_ticket as ticket',
            'product_payments.payment_voucher as voucher'
        )
        ->orderBy('orders.order_date', 'desc') // Ordenar por fecha descendente
        ->limit(100)
        ->get();

    // Incomes
    $incomes = DB::table('income_details')
        ->leftJoin('incomes', 'income_details.id', '=', 'incomes.income_details_id')
        ->leftJoin('employees', 'income_details.employee_id', '=', 'employees.id')
        ->leftJoin('students', 'income_details.student_id', '=', 'students.id')
        ->select(
            DB::raw("'type' as income_details"),
            'students.user_identification',
            'income_details.id as id',
            DB::raw("CONCAT(students.name, ' ', students.last_names) as student_name"),
            DB::raw("CONCAT(employees.name, ' ', employees.last_names) as income_by"),
            'income_details.payment_date as income_date',
            'income_details.payment_method',
            'incomes.concept',
            'incomes.discount',
            'incomes.total',
            'income_details.ticket_path as ticket',
            'income_details.file_path as voucher'
        )
        ->orderBy('income_details.payment_date', 'desc') // Ordenar por fecha descendente
        ->limit(100)
        ->get();

    // Flight Payments
    $flightPayments = DB::table('flight_payments')
        ->leftJoin('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
        ->leftJoin('payments', 'flight_payments.id', '=', 'payments.id_flight')
        ->leftJoin('payment_methods', 'payments.id_payment_method', '=', 'payment_methods.id')
        ->leftJoin('students', 'flight_payments.id_student', '=', 'students.id')
        ->leftJoin('employees', 'flight_payments.id_employee', '=', 'employees.id')
        ->select(
            DB::raw("'type' as flight_payments"),
            'students.user_identification',
            'flight_history.id',
            'students.name as student_name',
            'employees.name as income_by',
            'flight_payments.created_at as income_date',
            'payment_methods.type as payment_method',
            'flight_history.type_flight as concept',
            DB::raw("0.00 as discount"),
            'payments.amount as total',
            'payments.payment_ticket as ticket',
            'payments.payment_voucher as voucher'
        )
        ->orderBy('flight_payments.created_at', 'desc') // Ordenar por fecha descendente
        ->limit(100)
        ->get();

    // Recreative
    $recreative = DB::table('customer_payments')
        ->leftJoin('flight_customers', 'flight_customers.id', '=', 'customer_payments.id_customer_flight')
        ->leftJoin('payment_methods', 'payment_methods.id', '=', 'customer_payments.id_payment_method')
        ->leftJoin('employees', 'flight_customers.id_employee', '=', 'employees.id')
        ->select(
            DB::raw("'type' as customer_payments"),
            DB::raw("'N/A' as user_identification"),
            'customer_payments.id',
            DB::raw("'N/A' as student_name"),
            'employees.name as income_by',
            'customer_payments.created_at as income_date',
            'payment_methods.type as payment_method',
            'flight_customers.flight_type as concept',
            DB::raw("0.00 as discount"),
            'customer_payments.amount as total',
            'customer_payments.payment_ticket as ticket',
            'customer_payments.payment_voucher as voucher'
        )
        ->orderBy('customer_payments.created_at', 'desc')
        ->limit(100)
        ->get();


        // Combinar todas las colecciones en una
        $allData = $orders
            ->merge($incomes)
            ->merge($flightPayments)
            ->merge($recreative);

        $allData = $allData->sortByDesc('income_date');
        $allData = $allData->values();

        return response()->json($allData);
    }
}


