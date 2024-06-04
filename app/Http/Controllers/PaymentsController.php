<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;

class PaymentsController extends Controller
{
    function addPayment(Request $request){ 
        $data = $request->all();
        $student = $request['id_student'];
        return response()->json([
            'status' => 'success',
            'message' => $student
        ]);
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
