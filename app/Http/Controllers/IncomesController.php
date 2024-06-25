<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Income;
use App\Models\MonthlyPayment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomesController extends Controller
{

    public function extracted(mixed $payment, $employeeId): void
    {
            $income = new Income();
            $income->student_id = $payment['student_id'];
            $income->employee_id = $employeeId;
            $income->payment_date = $payment['payment_date'];
            $income->original_import = $payment['original_import'];
            $income->discount = $payment['discount'];
            $income->iva = $payment['iva'];
            $income->commission = $payment['commission'];
            $income->total = $payment['total'];
            $income->payment_method = $payment['payment_method'];
            $income->concept = $payment['concept'];
            $income->bank_account = $payment['bank_account'];
            $income->save();
    }

    public function generateTicket($data, $studentData, $employeeName, $employeeLastNames) {

        $pdf = PDF::loadView('income_ticket',
            ['data' => $data, 'studentData' => $studentData, 'employeeName' => $employeeName, 'employeeLastNames' => $employeeLastNames]
        );
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="generatedTicket.pdf"');
    }

    public function studentDataForTicket($data, $employeeName, $employeeLastNames): void
    {
        if(!empty($data) && isset($data[0]['student_id'])) {
            $studentId = $data[0]['student_id'];
            $studentDetails = Student::find($studentId);
            $this->generateTicket($data, $studentDetails, $employeeName, $employeeLastNames);
        }
    }


    public function createTuitionIncome(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array',
        ],
        [
            'payments.required' => 'Debe ingresar al menos un pago',
            'payments.array' => 'El pago debe ser un arreglo',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $employee = Employee::where('user_identification', $user->user_identification)->first();
        $this->studentDataForTicket($request->input('payments'), $employee->name, $employee->last_names);

        foreach ($request->input('payments') as $payment){
            $monthlyPayment = MonthlyPayment::find($payment['id']);
            $monthlyPayment->status = $payment['status'];
            $monthlyPayment->amount = $payment['status'] == 'paid' ? 0 : $monthlyPayment->amount-$payment['total'];
            $monthlyPayment->save();

            $this->extracted($payment, $employee->id);
        }

        return response()->json(['message' => 'ok'], 201);
    }

    public function createFlightCreditIncome(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array',
        ],
        [
            'payments.required' => 'Debe ingresar al menos un pago',
            'payments.array' => 'El pago debe ser un arreglo',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $employee = Employee::where('user_identification', $user->user_identification)->first();

        $this->studentDataForTicket($request->input('payments'), $employee->name, $employee->last_names);

        foreach ($request->input('payments') as $payment){
            $student = Student::find($payment['student_id']);
            if ($payment['concept'] === 'Horas Simulador') {
                $student->simulator_credit += $payment['quantity'];
            } else {
                $student->flight_credit += $payment['quantity'];
            }
            $student->save();

            $this->extracted($payment, $employee->id);
        }

        return response()->json(['message' => 'ok'], 201);
    }
}
