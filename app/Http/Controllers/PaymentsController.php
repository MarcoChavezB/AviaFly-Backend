<?php

namespace App\Http\Controllers;

use App\Models\FlightPayment;
use App\Models\Student;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{

    /*
    PAYLOAD {
        "id_flight": "4",
        "debt": "100",
        "amount": "1",
        "payment_method": "cash", "transfer", "flight_credit
    }
    */
    function addPayment(Request $request){ 
        $data = $request->all();

        if($data['amount'] > $data['debt']){
            return response()->json([
                'status' => 'error',
                'msg' => 'El monto de pago es mayor al monto de débito'
            ], 400);
        }        
        DB::statement('CALL installmentPayment(?,?, ?, ?)', [
            $data['id_student'], 
            $data['id_flight'], 
            $data['amount'], 
            $data['payment_method']
        ]);
        return response()->json([
            'status' => 'success',
            'msg' => 'Pago realizado con éxito'
        ], 200);
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
