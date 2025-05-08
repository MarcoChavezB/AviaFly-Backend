<?php

namespace App\Http\Controllers;

use App\Models\IncomePaymentMethod;
use Illuminate\Http\Request;

class IncomePaymentMethodController extends Controller
{
    public function index(){
      $paymentMethods = IncomePaymentMethod::all();
      if($paymentMethods->isEmpty()) {
          return response()->json([
            'msg' => 'No payment methods found',
            'data' => [],
            'success' => false
        ], 404);
      }

        return response()->json([
            'msg' => 'Payment methods retrieved successfully',
            'data' => $paymentMethods,
            'success' => true
        ], 200);
    }
}
