<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlightHistoryController extends Controller
{

    public function indexReport(int $id_flight)
    {
        $report = Student::select(
            'flight_history.id as id_flight',
            'flight_history.flight_status',
            'flight_payments.payment_status',
            'students.name',
            'students.last_names',
            'flight_history.type_flight',
            'flight_payments.total',
            'flight_history.initial_horometer',
            'flight_history.final_horometer',
            'flight_history.total_horometer',
            'flight_history.final_tacometer',
            'flight_history.comment',
            'flight_history.flight_date'
        )
            ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')
            ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
            ->where('flight_history.id', $id_flight)
            ->orderBy('flight_history.created_at', 'desc')
            ->groupBy(
                'students.name',
                'students.last_names',
                'flight_history.type_flight',
                'flight_payments.total',
                'flight_history.initial_horometer',
                'flight_history.final_horometer',
                'flight_history.total_horometer',
                'flight_history.final_tacometer',
                'flight_history.comment',
                'flight_history.flight_date',
                'flight_history.id',
                'flight_history.flight_status',
                'flight_payments.payment_status',
            )
            ->get();

        return response()->json($report, 200);
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
                'students.id',
                'flight_history.type_flight',
                'flight_history.flight_date',
                'flight_history.flight_hour',
                'flight_history.flight_status',
                'flight_payments.total',
                'payments.id_flight',
                'flight_payments.payment_status',
                'flight_payments.id'
            )
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
            'flight_history.flight_alone',
            'flight_history.initial_horometer',
            'flight_history.final_horometer',
            'flight_history.total_horometer',
            'flight_history.final_tacometer',
            'flight_history.comment',
            'flight_history.hours',
            'flight_history.equipo',
            'students.name',
            'students.last_names',
            'students.flight_credit',
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
                'flight_history.hours',
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
                'students.flight_credit',
                'flight_history.flight_alone',
                'flight_history.initial_horometer',
                'flight_history.equipo',
                'flight_history.final_horometer',
                'flight_history.total_horometer',
                'flight_history.final_tacometer',
                'flight_history.comment',
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
                'flight_credit' => $flights->flight_credit,
                'curp' => $flights->curp,
                'flight_type' => $flights->tipo_vuelo,
                'flight_date' => $flights->fecha_vuelo,
                'hour_flight' => $flights->hora_vuelo,
                'flight_hours' => $flights->hours,
                'equipo' => $flights->equipo,
                'flight_status' => $flights->status_vuelo,
                'payment_status' => $flights->status_pago,
                'total' => $flights->total_dinero,
                'total_amounts' => $flights->total_amounts,
                'debt' => $flights->deuda_viva,
                'flight_alone' => $flights->flight_alone,
                'initial_horometer' => $flights->initial_horometer,
                'final_horometer' => $flights->final_horometer,
                'total_horometer' => $flights->total_horometer,
                'final_tacometer' => $flights->final_tacometer,
                'comment' => $flights->comment,

                'history_amounts' => $history_amounts,
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


    /*
 *  Payload:
 *
    {
    "horometroInicial": 1,
    "horometroFinal": 4.5,
    "tacometro": "100",
    "comments": "ksoakosk",
    "flight_alone": true,
    "total_horometro": 3.5
    }

    */

    function storeReport(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'id_flight' => 'required|numeric',
            'horometroInicial' => 'required|numeric',
            'horometroFinal' => 'required|numeric',
            'tacometro' => 'required|string',
            'comments' => 'required|string',
            'flight_alone' => 'required|boolean',
            'total_horometro' => 'required|numeric',
        ], [
            'horometroInicial.required' => 'Campo requerido',
            'horometroFinal.required' => 'Campo requerido',
            'horoemtroInicial.numeric' => 'Dato incorrecto',
            'horometroFinal.numeric' => 'Dato incorrecto',
            'tacometro.required' => 'Campo requerido',
            'comments.required' => 'Campo requerido',
            'flight_alone.required' => 'Campo requerido',
            'total_horometro.required' => 'Campo requerido',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $flight = flightHistory::find($data['id_flight']);

        $flight->flight_alone = $data['flight_alone'];
        $flight->initial_horometer = $data['horometroInicial'];
        $flight->final_horometer = $data['horometroFinal'];
        $flight->total_horometer = $data['total_horometro'];
        $flight->final_tacometer = $data['tacometro'];
        $flight->comment = $data['comments'];

        $flight->save();

        return response()->json([
            'msg' => "El reporte se ha guardado correctamente"
        ]);
    }
    
    /**
        title: flight_type
        start:fligt_dateTflight_hour
        end: fligt_dateTflight_hour + flight_hours
    */
    function getFLightReservations(){
        $flights = FlightHistory::select('flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours')
            ->groupBy('flight_history.type_flight', 'flight_history.flight_date', 'flight_history.flight_hour', 'flight_history.hours')
            ->get();

        $flights = $flights->map(function($flight) {
            $start = Carbon::createFromFormat('Y-m-d H:i', $flight->flight_date . ' ' . $flight->flight_hour);
            
            $end = $start->copy()->addHours($flight->hours);
            
            return [
                'title' => $flight->type_flight,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ];
        });

        return response()->json($flights);
    }
}
