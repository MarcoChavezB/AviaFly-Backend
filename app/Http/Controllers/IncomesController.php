<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IncomesController extends Controller
{
    public function CreateTuitionIncome(Request $request){
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer|exists:students,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'payments' => 'required|array',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $employeeId = Employee::where('user_identification', $user->user_identification)->first()->id;

        foreach ($request->input('payments') as $payment){
            $monthlyPayment = MonthlyPayment::find($payment['id']);
            $monthlyPayment->status = $payment['status'];
            $monthlyPayment->amount -= $payment['total'];
            $monthlyPayment->save();

            $income = new Income();
            $income->student_id = $request->student_id;
            $income->employee_id = $employeeId;
            $income->payment_date = $payment['payment_date'];
            $income->original_import = $payment['original_import'];
            $income->discount = $payment['discount'];
        }
    }
}
