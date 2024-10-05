<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Base;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\Income;
use App\Models\IncomeDetails;
use App\Models\MonthlyPayment;
use App\Models\PaymentMethod;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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
        $fileController = new FileController();
        $studentData = Student::findOrFail($studentID);
        $baseData = Base::findOrFail($employeeBaseId);
        $incomeDetails = IncomeDetails::findOrFail($incomeDetailsId);

        $pdf = PDF::loadView('income_ticket', compact('data', 'studentData', 'employeeName', 'employeeLastNames', 'baseData', 'incomeDetails'));

        $baseName = $this->sanitizeName($baseData->name);
        $fileName = $this->generateFileName($baseName, $studentData->user_identification, 'tickets');
        $pdf->save(public_path($fileName));

        $incomeDetails->update(['ticket_path' => $fileController->generateManualUrl($fileName)]);

        return $fileController->generateManualUrl($fileName);
    }

    public function saveVoucher($file, int $baseId, int $studentId): string
    {

        $fileController = new FileController();

        $base = Base::findOrFail($baseId);
        $student = Student::findOrFail($studentId);

        $baseName = $this->sanitizeName($base->name);
        $extension = $file->getClientOriginalExtension();

        $fileName = $this->generateFileName($baseName, $student->user_identification, 'vouchers', $extension);

        $file->move(public_path(dirname($fileName)), basename($fileName));
        return  $fileController->generateManualUrl($fileName);
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
        switch ($type) {
            case "Horas de vuelo";
                $student->flight_credit += $credit;
                break;
            case "Horas de vuelo (VIEJO)":
                $student->flight_credit += $credit;
                break;
            case "Horas simulador":
                $student->simulator_credit += $credit;
                break;
            case "Horas simulador (VIEJO)":
                $student->simulator_credit += $credit;
                break;
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
            ->join('employees', 'income_details.employee_id', '=', 'employees.id')
            ->join('students', 'income_details.student_id', '=', 'students.id')
            ->select('students.user_identification as student_registration',
                DB::raw("CONCAT(students.name, ' ', students.last_names) as student_name"),
                'incomes.id as income_id',
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
                'income_details.ticket_path as ticket');

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
        $paymentMethods = PaymentMethod::all();
        $bankAccounts = BankAccount::all();
        $discounts = Discount::all();

        return response()->json([
            'incomes' => $incomes->items(),
            'pagination_data' => $paginationData,
            'bases' => $bases,
            'paymentMethods' => $paymentMethods,
            'discounts' => $discounts,
            'bankAccounts' => $bankAccounts
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



    /*
 *      Payload:
     * {
            "income_id": 1,
            "payment_date": "2024-10-02",
            "payment_method": "Efectivo",
            "bankAccount": "",
            "amount": "3612.00", // original_import
            "selectedDiscount": "",
            "discountValue": 0,
            "iva": "688.00",
            "total": "4300.00",
            "comision": "0.00"
        }
     */
    public function update(Request $request)
    {
        $request->validate([
            'income_id' => 'required|exists:incomes,id',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'bankAccount' => 'nullable|string|max:50',
            'amount' => 'nullable|numeric',
            'selectedDiscount' => 'nullable|string',
            'discountValue' => 'nullable|numeric',
            'iva' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'comision' => 'nullable|numeric',
        ]);

        $income = Income::find($request->income_id);
        $incomeDetail = $income->incomeDetails;

        if($income){
            $income->original_import = $request->amount ?? $income->original_import;
            $income->discount = $request->discountValue ?? $income->discount;
            $income->iva = $request->iva ?? $income->iva;
            $income->total = $request->total ?? $income->total;

            $incomeDetail->commission = $request->comision ?? $incomeDetail->commission;
            $incomeDetail->payment_method = $request->payment_method ?? $incomeDetail->payment_method;
            $incomeDetail->bank_account = $request->bankAccount ?? $incomeDetail->bank_account;
            $incomeDetail->total = $request->total ?? $incomeDetail->total;
            $incomeDetail->payment_date = $request->payment_date ?? $incomeDetail->payment_date;
        }
        $income->save();
        $incomeDetail->save();
        return response()->json($incomeDetail, 200);
    }

public function delete($id_income){
    $request = request(); // Obtener el request actual
    $income = Income::find($id_income);

    if (!$income) {
        return response()->json("income not found");
    }

    $incomeDetail = $income->incomeDetails;
    if (!$incomeDetail) {
        return response()->json("income detail not found");
    }

    $student = Student::find($incomeDetail->student_id);
    if (!$student) {
        return response()->json("student not found");
    }

    // Ajustar créditos según el concepto
    if ($income->concept == "Horas de vuelo") {
        $student->flight_credit -= $income->quantity;
        $student->save();
    }

    if ($income->concept == "Horas simulador") {
        $student->simulator_credit -= $income->quantity;
        $student->save();
    }

    $fileController = new FileController();
    $deletedFiles = []; // Array para almacenar los archivos eliminados
    if ($incomeDetail->file_path) {
        $fileController->deleteFile($incomeDetail->file_path);
        $deletedFiles[] = $incomeDetail->file_path; // Registrar el archivo eliminado
    }
    if ($incomeDetail->ticket_path) {
        $fileController->deleteFile($incomeDetail->ticket_path);
        $deletedFiles[] = $incomeDetail->ticket_path; // Registrar el archivo eliminado
    }

    // Eliminar el ingreso
    $income->delete();

    // Información del empleado que realizó la eliminación
    $employee = $request->user(); // Obtener el usuario autenticado
    $employeeInfo = [
        'id' => $employee->id ?? 'N/A',
        'name' => $employee->name ?? 'N/A', // Asegúrate de que el modelo tiene este campo
        'user_identification' => $employee->user_identification ?? 'N/A', // Identificación del empleado
        'user_type' => $employee->user_type ?? 'N/A', // Tipo de usuario
    ];

    // Crear un mensaje de log detallado
    Log::channel('slack')->info('Ingreso eliminado', [
        'income_id' => $income->id,
        'concept' => $income->concept,
        'quantity' => $income->quantity,
        'student_id' => $student->id,
        'student_name' => $student->name ?? 'N/A', // Asegúrate de que este campo existe
        'deleted_files' => $deletedFiles,
        'timestamp' => now()->toString(), // Marca de tiempo de la operación
        'employee' => $employeeInfo, // Información del empleado
    ]);

    return response()->json(["income" => $income, "incomeDetail" => $incomeDetail]);
}
}

