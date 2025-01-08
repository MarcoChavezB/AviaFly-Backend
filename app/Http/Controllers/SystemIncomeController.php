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
            ->join('product_payments', 'product_payments.id_order', '=', 'orders.id')
            ->join('order_details', 'orders.id', '=', 'order_details.id_order')
            ->join('products', 'order_details.id_product', '=', 'products.id')
            ->join('payment_methods', 'product_payments.id_payment_method', '=', 'payment_methods.id')
            ->leftJoin('students', 'orders.id_client', '=', 'students.id')
            ->join('employees', 'orders.id_employe', '=', 'employees.id')
            ->select(
                'students.user_identification',
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
            ->get();

        // Incomes
        $incomes = DB::table('income_details')
            ->join('incomes', 'income_details.id', '=', 'incomes.income_details_id')
            ->join('employees', 'income_details.employee_id', '=', 'employees.id')
            ->join('students', 'income_details.student_id', '=', 'students.id')
            ->select(
                'students.user_identification',
                'incomes.id as id',
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
            ->get();

        // Flight Payments
        $flightPayments = DB::table('flight_payments')
            ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->join('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->join('payment_methods', 'payments.id_payment_method', '=', 'payment_methods.id')
            ->join('students', 'flight_payments.id_student', '=', 'students.id')
            ->join('employees', 'flight_payments.id_employee', '=', 'employees.id')
            ->select(
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
            ->get();

        // Recreative
        $recreative = DB::table('customer_payments')
            ->join('flight_customers', 'flight_customers.id', '=', 'customer_payments.id_customer_flight')
            ->join('payment_methods', 'payment_methods.id', '=', 'customer_payments.id_payment_method')
            ->join('employees', 'flight_customers.id_employee', '=', 'employees.id')
            ->select(
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


