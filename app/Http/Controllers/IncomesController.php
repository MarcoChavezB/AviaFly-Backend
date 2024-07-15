<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Employee;
use App\Models\Income;
use App\Models\IncomeDetails;
use App\Models\MonthlyPayment;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class IncomesController extends Controller
{

    public function saveIncomeDetails(array $paymentDetails, int $employeeId): int
    {
        $income = IncomeDetails::create([
            'employee_id' => $employeeId,
            'payment_date' => $paymentDetails['payment_date'],
            'student_id' => $paymentDetails['student_id'],
            'commission' => $paymentDetails['commission'],
            'payment_method' => $paymentDetails['payment_method'],
            'bank_account' => $paymentDetails['bank_account'],
            'file_path' => $paymentDetails['file_path'],
            'total' => $paymentDetails['total']
        ]);

        return $income->id;
    }

    private function sanitizeName(string $name): string
    {
        return str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $name);
    }

    public function generateTicket(array $data, string $employeeName, string $employeeLastNames, int $employeeBaseId, int $incomeDetailsId, int $studentID): string
    {
        $studentData = Student::findOrFail($studentID);
        $baseData = Base::findOrFail($employeeBaseId);
        $incomeDetails = IncomeDetails::findOrFail($incomeDetailsId);

        $pdf = PDF::loadView('income_ticket', compact('data', 'studentData', 'employeeName', 'employeeLastNames', 'baseData', 'incomeDetails'));

        $baseName = $this->sanitizeName($baseData->name);
        $fileName = "bases/{$baseName}/{$studentData->user_identification}/tickets/ticket_" . time() . '.pdf';
        $pdf->save(public_path($fileName));

        $incomeDetails->update(['ticket_path' => url($fileName)]);

        return url($fileName);
    }

    public function saveVoucher($file, int $baseId, int $studentId): string
    {
        $base = Base::findOrFail($baseId);
        $student = Student::findOrFail($studentId);

        $baseName = $this->sanitizeName($base->name);

        $fileName = "bases/{$baseName}/{$student->user_identification}/vouchers/voucher_" . time() . '.' . $file->getClientOriginalExtension();
        $file->save(public_path($fileName));

        return url($fileName);
    }


    public function createFlightCreditIncome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array',
            'payment_date' => 'required|date',
            'student_id' => 'required|integer',
            'commission' => 'required|numeric',
            'payment_method' => 'required|string',
            'bank_account' => 'required|string',
            'file' => 'sometimes|file|mimes:jpg, jpeg, png',
            'total' => 'required|numeric',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $employee = Employee::where('user_identification', $user->user_identification)->first();
        $voucherPath = null;

        if($request->hasFile('file')){
            $voucherPath = $this->saveVoucher($request->file('file'), $employee->id_base, $request->input('student_id'));
        }

        $paymentDetails = $request->only(['payment_date', 'student_id', 'commission', 'payment_method', 'bank_account', 'total']);
        $paymentDetails['file_path'] = $voucherPath;
        $incomeDetailsId = $this->saveIncomeDetails($paymentDetails, $employee->id);


        foreach ($request->input('payments') as $payment){
            $income = new Income();
            $income->iva = $payment['iva'];
            $income->discount = $payment['discount'];
            $income->total = $payment['total'];
            $income->original_import = $payment['original_import'];
            $income->concept = $payment['concept'];
            $income->income_details_id = $incomeDetailsId;
            $income->save();
        }

        $ticketUrl = $this->generateTicket($request->input('payments'), $employee->name,
            $employee->last_names, $employee->id_base, $incomeDetailsId, $request->input('student_id') );

        return response()->json(['message' => 'ok', 'ticketUrl' => $ticketUrl], 201);
    }










   /* public function createTuitionIncome(Request $request): JsonResponse
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
    }*/
}
