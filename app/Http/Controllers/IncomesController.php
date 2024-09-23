<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Employee;
use App\Models\Income;
use App\Models\IncomeDetails;
use App\Models\MonthlyPayment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $search = ['á', 'é', 'í', 'ó', 'ú'];
        $replace = ['a', 'e', 'i', 'o', 'u'];
        return strtolower(str_replace($search, $replace, $name));
    }

    private function generateFileName(string $baseName, string $userIdentification, string $type, ?string $extension = 'pdf'): string
    {
        $prefix = ($type === 'tickets') ? 'ticket_' : 'voucher_';
        return "bases/{$baseName}/{$userIdentification}/{$type}/{$prefix}" . time() . '.' . $extension;
    }


    public function generateTicket(array $data, string $employeeName, string $employeeLastNames, int $employeeBaseId, int $incomeDetailsId, int $studentID): string
    {
        $studentData = Student::findOrFail($studentID);
        $baseData = Base::findOrFail($employeeBaseId);
        $incomeDetails = IncomeDetails::findOrFail($incomeDetailsId);

        $pdf = PDF::loadView('income_ticket', compact('data', 'studentData', 'employeeName', 'employeeLastNames', 'baseData', 'incomeDetails'));

        $baseName = $this->sanitizeName($baseData->name);
        $fileName = $this->generateFileName($baseName, $studentData->user_identification, 'tickets');
        $pdf->save(public_path($fileName));

        $incomeDetails->update(['ticket_path' => url($fileName)]);

        return asset($fileName);
    }

    public function saveVoucher($file, int $baseId, int $studentId): string
    {
        $base = Base::findOrFail($baseId);
        $student = Student::findOrFail($studentId);

        $baseName = $this->sanitizeName($base->name);
        $extension = $file->getClientOriginalExtension();

        $fileName = $this->generateFileName($baseName, $student->user_identification, 'vouchers', $extension);

        $file->move(public_path(dirname($fileName)), basename($fileName));

        return asset(str_replace(public_path(), '', $fileName));
    }


    public function createIncomes(Request $request)
    {
        $this->validateRequest($request);

        $employee = $this->getAuthenticatedEmployee();

        $voucherPath = $this->handleFileUpload($request, $employee->id_base, $request->input('student_id'));

        $paymentDetails = $this->extractPaymentDetails($request, $voucherPath);
        $incomeDetailsId = $this->saveIncomeDetails($paymentDetails, $employee->id);

        $this->saveIncomeEntries($request->input('payments'), $incomeDetailsId, $request->input('student_id'));

        $ticketUrl = $this->generateTicket($request->input('payments'), $employee->name,
            $employee->last_names, $employee->id_base, $incomeDetailsId, $request->input('student_id'));

        return response()->json(['message' => 'ok', 'ticketUrl' => $ticketUrl], 201);
    }

    private function validateRequest(Request $request)
    {
        $request->merge(['payments' => json_decode($request->input('payments'), true)]);

        Validator::make($request->all(), [
            'payments' => 'required|array',
            'payment_date' => 'required|date',
            'student_id' => 'required|integer',
            'commission' => 'sometimes|numeric',
            'payment_method' => 'required|string',
            'bank_account' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'total' => 'required|numeric',
        ], [
            'payments.required' => 'Debe ingresar al menos un pago',
            'payments.array' => 'El pago debe ser un arreglo',
            'payment_date.required' => 'Debe ingresar la fecha de pago',
            'payment_date.date' => 'La fecha de pago debe ser una fecha válida',
            'student_id.required' => 'Debe ingresar el id del estudiante',
            'student_id.integer' => 'El id del estudiante debe ser un número entero',
            'commission.numeric' => 'La comisión debe ser un número',
            'payment_method.required' => 'Debe ingresar el método de pago',
            'payment_method.string' => 'El método de pago debe ser una cadena',
            'bank_account.string' => 'La cuenta bancaria debe ser una cadena',
            'file.file' => 'El archivo debe ser un archivo',
            'file.mimes' => 'El archivo debe ser una imagen o un pdf',
            'total.required' => 'Debe ingresar el total',
            'total.numeric' => 'El total debe ser un número',
        ])->validate();
    }

    private function getAuthenticatedEmployee(): Employee
    {
        $user = Auth::user();
        return Employee::where('user_identification', $user->user_identification)->first();
    }

    private function handleFileUpload(Request $request, int $baseId, int $studentId): ?string
    {
        if ($request->hasFile('file')) {
            return $this->saveVoucher($request->file('file'), $baseId, $studentId);
        }
        return null;
    }

    private function extractPaymentDetails(Request $request, ?string $voucherPath): array
    {
        $paymentDetails = $request->only(['payment_date', 'student_id', 'commission', 'payment_method', 'bank_account', 'total']);
        $paymentDetails['file_path'] = $voucherPath;
        return $paymentDetails;
    }

    private function saveIncomeEntries(array $payments, int $incomeDetailsId, $studentId)
    {
        foreach ($payments as $payment) {
            Income::create([
                'iva' => $payment['iva'],
                'discount' => $payment['discount'],
                'total' => $payment['total'],
                'original_import' => $payment['original_import'],
                'concept' => $payment['concept'],
                'quantity' => $payment['quantity'],
                'income_details_id' => $incomeDetailsId,
            ]);

             if($payment['its_simulator_or_flight']){
                    $this->updateStudentCredits($studentId, $payment['quantity'], $payment['concept']);
             }else if($payment['its_monthly_payment']){
                 $this->updateStudentMonthlyPayments($payment['monthly_payment_id'], $payment['new_status_for_monthly_payment'], $payment['total']);
             }
        }
    }

    private function updateStudentCredits($studentId, $credit, $type){
        $student = Student::find($studentId);
        if($type == 'Credito de vuelo'){
            $student->flight_credit += $credit;
        }else{
            $student->simulator_credit += $credit;
        }
        $student->save();
    }

    private function updateStudentMonthlyPayments($monthlyPaymentId, $newStatus, $amount){
        $monthlyPayment = MonthlyPayment::find($monthlyPaymentId);
        $monthlyPayment->status = $newStatus;
        if ($newStatus == 'paid') {
            $monthlyPayment->amount = 0;
        } else {
            $monthlyPayment->amount = $monthlyPayment->amount - $amount;
        }
        $monthlyPayment->save();
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $startDate = $request->get('start_date', null);
        $endDate = $request->get('end_date', null);
        $studentFilter = $request->get('student_filter', null);
        $baseFilter = $request->get('base_filter', 0);

        $query = DB::table('income_details')
            ->join('incomes', 'income_details.id', '=', 'incomes.income_details_id')
            ->join('students', 'income_details.student_id', '=', 'students.id')
            ->select('students.user_identification as student_registration',
                DB::raw("CONCAT(students.name, ' ', students.last_names) as student_name"),
                'income_details.payment_date',
                'income_details.total',
                'income_details.payment_method',
                'income_details.id as income_details_id',
                'incomes.id as income_id',
                'incomes.concept');

        if ($startDate && $endDate) {
            $query->whereBetween('income_details.payment_date', [$startDate, $endDate]);
        }

        if ($studentFilter) {
            $query->where(function ($query) use ($studentFilter) {
                $query->where('students.user_identification', 'like', '%' . $studentFilter . '%')
                    ->orWhere(DB::raw("CONCAT(students.name, ' ', students.last_names)"), 'like', '%' . $studentFilter . '%');
            });
        }

        if ($baseFilter != 0) {
            $query->join('employees', 'income_details.employee_id', '=', 'employees.id')
                ->where('employees.id_base', $baseFilter);
        }

        $incomes = $query->orderBy('income_details.payment_date', 'desc')
            ->paginate($perPage);

        $paginationData = [
            'current_page' => $incomes->currentPage(),
            'has_next_page' => $incomes->hasMorePages(),
            'total_records' => $incomes->total(),
            'displaying_from' => $incomes->firstItem(),
            'displaying_to' => $incomes->lastItem(),
        ];

        $bases = Base::all();

        return response()->json([
            'incomes' => $incomes->items(),
            'pagination_data' => $paginationData,
            'bases' => $bases
        ]);
    }

    public function show($id){
        $income = DB::table('income_details')
            ->join('incomes', 'income_details.id', '=', 'incomes.income_details_id')
            ->join('employees', 'income_details.employee_id', '=', 'employees.id')
            ->join('students', 'income_details.student_id', '=', 'students.id')
            ->select('students.user_identification as student_registration',
                DB::raw("CONCAT(students.name, ' ', students.last_names) as student_name"),
                'income_details.payment_date',
                'incomes.concept',
                'income_details.payment_method',
                'income_details.file_path as voucher',
                'incomes.discount',
                'incomes.original_import as subtotal',
                'income_details.total',
                DB::raw("CONCAT(employees.name, ' ', employees.last_names) as employee_name"),
                'income_details.bank_account',
                'incomes.iva as iva',
                'income_details.commission',
                'income_details.ticket_path as ticket',
                )
            ->where('incomes.id', $id)
            ->first();

        return response()->json($income);
    }










}
