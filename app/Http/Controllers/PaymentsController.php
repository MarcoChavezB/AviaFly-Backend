<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Corrección aquí
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    function addPayment(Request $request){ // Y aquí también
        $data = $request->all();
        DB::statement('CALL installmentPayment(?, ?, ?)', [
            $data['id_flight'], 
            $data['amount'], 
            $data['payment_method']
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Payment added successfully'
        ]);
    }
}
