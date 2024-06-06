<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlightHistoryController extends Controller
{
    function isDateReserved(Request $request)
    {
        $data = $request->all();
        $date = $data['date'];
        $id_instructor = $data['id_instructor'];
        $flight_type = $data['flight_type'];
    }

    function flightsData(int $id_student)
    {
        // Consulta principal para obtener los detalles del vuelo y los totales
        $flights = FlightPayment::select(
            'flight_payments.id as id_flight',
            'students.id as id_student',
            'students.curp',
            'flight_history.type_flight as tipo_vuelo',
            'flight_history.flight_date as fecha_vuelo',
            'flight_history.flight_hour as hora_vuelo',
            'flight_payments.payment_status as status_pago',
            DB::raw('flight_history.flight_status as status_vuelo'),
            'flight_payments.total as total_dinero',
            DB::raw('COALESCE(SUM(payments.amount), 0) as total_amounts'),
            DB::raw('flight_payments.total - COALESCE(SUM(payments.amount), 0) as deuda_viva'),
            'payments.id_flight'
        )
            ->leftJoin('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->leftJoin('students', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->where('students.id', $id_student)
            ->groupBy(
                'students.curp',
                'students.id', // Aquí se elimina el alias
                'flight_history.type_flight',
                'flight_history.flight_date',
                'flight_history.flight_hour',
                'flight_history.flight_status',
                'flight_payments.total',
                'payments.id_flight',
                'flight_payments.payment_status',
                'flight_payments.id')
            ->OrderBy('flight_history.created_at', 'desc')
            ->get();

        $data = $flights->map(function ($flights) {
            $history_amounts = DB::table('payments')
                ->select('amount', 'created_at')
                ->where('id_flight', $flights->id_flight)
                ->get();

            return [
                'id_flight' => $flights->id_flight,
                'id_student' => $flights->id_student,
                'curp' => $flights->curp,
                'flight_type' => $flights->tipo_vuelo,
                'flight_date' => $flights->fecha_vuelo,
                'hour_flight' => $flights->hora_vuelo,
                'flight_status' => $flights->status_vuelo,
                'payment_status' => $flights->status_pago,
                'total' => $flights->total_dinero,
                'total_amounts' => $flights->total_amounts,
                'debt' => $flights->deuda_viva,
                'history_amounts' => $history_amounts
            ];
        });

        return response()->json($data, 200);
    }

    function reportDataById(int $id_flight)
    {
        $flights = FlightPayment::select(
            'flight_payments.id as id_flight',
            'students.curp',

            'students.name',
            'students.last_names',
            'students.credit',

            'flight_history.type_flight as tipo_vuelo',
            'flight_history.flight_date as fecha_vuelo',
            'flight_history.flight_hour as hora_vuelo',
            'flight_payments.payment_status as status_pago',
            DB::raw('flight_history.flight_status as status_vuelo'),
            'flight_payments.total as total_dinero',
            DB::raw('COALESCE(SUM(payments.amount), 0) as total_amounts'),
            DB::raw('flight_payments.total - COALESCE(SUM(payments.amount), 0) as deuda_viva'),
            'payments.id_flight'
        )
            ->leftJoin('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->leftJoin('students', 'students.id', '=', 'flight_payments.id_student')
            ->leftJoin('payments', 'flight_payments.id', '=', 'payments.id_flight')
            ->where('flight_payments.id', $id_flight)
            ->groupBy(
                'students.curp',
                'flight_history.type_flight',
                'flight_history.flight_date',
                'flight_history.flight_hour',
                'flight_history.flight_status',
                'flight_payments.total',
                'payments.id_flight',
                'flight_payments.payment_status',
                'flight_payments.id',
                'students.name',
                'students.last_names',
                'students.credit',
            )
            ->get();
        $data = $flights->map(function ($flights) {
            $history_amounts = DB::table('payments')
                ->select('amount', 'payment_method', 'created_at')
                ->where('id_flight', $flights->id_flight)
                ->get();

            return [
                'id_flight' => $flights->id_flight,
                'name' => $flights->name,
                'last_names' => $flights->last_names,
                'creadit' => $flights->credit,
                'curp' => $flights->curp,
                'flight_type' => $flights->tipo_vuelo,
                'flight_date' => $flights->fecha_vuelo,
                'hour_flight' => $flights->hora_vuelo,
                'flight_status' => $flights->status_vuelo,
                'payment_status' => $flights->status_pago,
                'total' => $flights->total_dinero,
                'total_amounts' => $flights->total_amounts,
                'debt' => $flights->deuda_viva,
                'history_amounts' => $history_amounts
            ];
        });

        return response()->json($data, 200);
    }

    function changeStatusFlight(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id_flight' => 'required|integer',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $flight = flightHistory::find($data['id_flight']);
        if ($flight->flight_status == $data['status']) {
            return response()->json([
                'msg' => 'El vuelo ya está en el estado solicitado'
            ], 400);
        }
        $flight->flight_status = $data['status'];
        $flight->save();
        return response()->json([
            'msg' => 'El vuelo se ha modificado correctamente'
        ], 200);
    }
}
