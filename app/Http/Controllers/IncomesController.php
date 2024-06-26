<?php

namespace App\Http\Controllers;

use App\Models\Base;
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

    public function studentDataForTicket($data)
    {
        if(!empty($data) && isset($data[0]['student_id'])) {
            $studentId = $data[0]['student_id'];
            return Student::find($studentId);
        }
    }

    public function baseDataForTicket($id){
        return Base::find($id);
    }

    public function generateTicket($data, $employeeName, $employeeLastNames, $employeeBaseId) {

        $studentData = $this->studentDataForTicket($data);
        $baseData = $this->baseDataForTicket($employeeBaseId);

        $pdf = PDF::loadView('income_ticket',
            ['data' => $data, 'studentData' => $studentData, 'employeeName' => $employeeName,
                'employeeLastNames' => $employeeLastNames, 'baseData' => $baseData]
        );

        $fileName = 'tickets/'.$baseData->name.'/ticket_' . $studentData->user_identification . '_' . time() . '.pdf';
        $pdf->save(public_path($fileName));

        return url($fileName);
    }

    public function createFlightCreditIncome(Request $request)
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

        $ticketUrl = $this->generateTicket($request->input('payments'), $employee->name, $employee->last_names, $employee->id_base);

        return response()->json(['message' => 'ok', 'ticketUrl' => $ticketUrl], 201);
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
}
