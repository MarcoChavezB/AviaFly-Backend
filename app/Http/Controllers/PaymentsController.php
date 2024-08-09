<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\Payments;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends Controller
{

    /*
    PAYLOAD {
        "id_flight_history": "1",
        "installment_value": "100",
        "payment_method": "1",
        "flight_status": "proceso, hecho, cancelado",
        "file_transfer": "file.jpg"
    }
    */
    function addPayment(Request $request){
        $data = $request->all();

        $validator = Validator::make($data, [
            'id_flight_history' => 'required',
            'installment_value' => 'required',
            'payment_method' => 'required',
            'flight_status' => 'required',
            'file_transfer' => 'mime:jpeg,png,jpg,pdf'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'msg' => 'Error en los datos enviados',
                'errors' => $validator->errors()
            ], 400);
        }

        $flight = FlightPayment::where('id_flight', $data['id_flight_history'])->first();
        $flightHistory = flightHistory::find($flight->id_flight);

        $student = Student::find($flight->id_student);

        $total_flight = $flight->total;

        if($data['installment_value'] > $total_flight){
            return response()->json([
                'status' => 'error',
                'msg' => 'El valor de la cuota no puede ser mayor al total del vuelo'
            ], 400);
        }

        $PaymentMethodController = new PaymentMethodController();
        $PDFController = new PDFController();

        if($data['payment_method'] == $PaymentMethodController->getCreditoVueloId()
            && $data['installment_value'] > $student->credit){
            return response()->json([
                'status' => 'error',
                'msg' => 'El valor de la cuota no puede ser mayor al crédito de vuelo del estudiante'
            ], 400);
        }

        if($flightHistory->flight_status == 'cancelado'){
            return response()->json([
                'status' => 'error',
                'msg' => 'No se puede realizar pagos en vuelos cancelados'
            ], 400);
        }

        $hasFileTransfer = !empty($data['file_transfer']);

        if($data['payment_method'] == $PaymentMethodController->getTransferenciaId() &&
            !$hasFileTransfer){
            return response()->json([
                'status' => 'error',
                'msg' => 'Debe adjuntar el comprobante de transferencia'
            ], 400);
        }

        $payment = new Payments();
        $payment->amount = $data['installment_value'];
        $payment->id_flight = $data['id_flight_history'];
        $payment->id_payment_method = $data['payment_method'];
        $payment->payment_voucher = $hasFileTransfer ? $data['file_transfer'] : null;
        $payment->save();

        $totalInstallments = Payments::where('id_flight', $data['id_flight_history'])->sum('amount');

        if($totalInstallments >= $total_flight){
            $flight->payment_status = 'pagado';
            $flight->save();
        }

        $PDFController->generateTicket($this->queryTicket($data['id_flight_history']));

        return response()->json($totalInstallments);
    }

    function queryTicket($id_flight_history){

    }




    /*
        PAYLOAD {
            "id_flight": "4",
            "status": enum('pending','paid','canceled','owed')
        }
    */
    function changeFlightPaymentStatus(Request $request){
        $data = $request->all();
        $flight = FlightPayment::find($data['id_flight']);
        $flight->payment_status = $data['status'];
        $flight->save();
        return response()->json([
            'status' => 'success',
            'msg' => 'Pago actualizado con éxito'
        ], 200);
    }
}
