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
use App\Models\Product;
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


    public function generateTicket(
        string $date,
        array $data,
        string $employeeName,
        string $employeeLastNames,
        int $employeeBaseId,
        int $incomeDetailsId,
        int $studentID,
        bool $hasExtraHour,
        $uniforms = []
    ): string
    {
        $fileController = new FileController();
        $studentData = Student::findOrFail($studentID);
        $baseData = Base::findOrFail($employeeBaseId);
        $incomeDetails = IncomeDetails::findOrFail($incomeDetailsId);


        $pdf = PDF::loadView('income_ticket', compact(
            'date',
            'data',
            'studentData',
            'employeeName',
            'employeeLastNames',
            'baseData',
            'incomeDetails',
            'hasExtraHour',
            'uniforms'
        ));

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
        $student = Student::find($request->student_id);
        $extraHour = false;

        $allUniforms = [];

        // agregar la hora extra si es mayor a 10 las horas compradas
        $payments = $request->payments; // Hacemos una copia del arreglo
        $uniform = null;

        foreach ($payments as &$payment) { // Usamos referencia para modificar la copia
            $payment['hasExtraHour'] = false;

            if ($payment['concept'] == "Horas simulador" && $payment['quantity'] >= 10) {
                $student->simulator_credit = $student->simulator_credit + 1;
                $payment['hasExtraHour'] = true;
                $extraHour = true;
            }

            if ($payment['concept'] == "Horas de vuelo" && $payment['quantity'] >= 10) {
                $student->flight_credit = $student->flight_credit + 1;
                $payment['hasExtraHour'] = true;
                $extraHour = true;
            }

            if (isset($payment['uniforms']) && is_array($payment['uniforms'])) {

                // agregar los uniformes encontrados en el arreglo
                $allUniforms = array_merge($allUniforms, $payment["uniforms"]);

                // transaction para restar el stock del producto
                DB::transaction(function () use ($payment) {
                    foreach ($payment['uniforms'] as $uniform) {
                        $product = Product::find($uniform['id']);

                        if ($product) {
                            $product->stock = max(0, $product->stock - 1); // Restar 1 siempre
                            $product->save();
                        }
                    }
                });
            }
        }

        $student->save();
        $employee = $this->getAuthenticatedEmployee();


        $voucherPath = $this->handleFileUpload($request, $employee->id_base, $request->input('student_id'));

        $paymentDetails = $this->extractPaymentDetails($request, $voucherPath);
        $incomeDetailsId = $this->saveIncomeDetails($paymentDetails, $employee->id);


        $this->saveIncomeEntries($payments, $incomeDetailsId, $request->input('student_id'));

        $ticketUrl = $this->generateTicket(
            $request->payment_date,
            $payments,
            $employee->name,
            $employee->last_names,
            $employee->id_base,
            $incomeDetailsId,
            $request->input('student_id'),
            $extraHour,
            $allUniforms
        );

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
        ->select(
            'students.user_identification as student_registration',
            DB::raw("CONCAT(students.name, ' ', students.last_names) as student_name"),
            'incomes.id as income_id',
            'income_details.payment_date',
            'incomes.concept',
            'income_details.payment_method',
            'income_details.file_path as voucher',
            'incomes.discount',
            'incomes.original_import as subtotal',
            DB::raw("SUM(COALESCE(incomes.total, 0) + COALESCE(income_details.commission, 0)) as total"),
            DB::raw("CONCAT(employees.name, ' ', employees.last_names) as employee_name"),
            'income_details.bank_account',
            'incomes.iva as iva',
            'income_details.commission',
            'income_details.ticket_path as ticket'
        )
        ->groupBy(
            'students.user_identification',
            'students.name',
            'students.last_names',
            'incomes.id',
            'income_details.payment_date',
            'incomes.concept',
            'income_details.payment_method',
            'income_details.file_path',
            'incomes.discount',
            'incomes.original_import',
            'employees.name',
            'employees.last_names',
            'income_details.bank_account',
            'incomes.iva',
            'income_details.commission',
            'income_details.ticket_path'
        );

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

    /*
        * Retorna todos los ingresos registrados en el sistema
        *
        * Incomes, flight, orders (products)
        *
        * Recreatives
        *
     * */

    public function indexIncomes() {
        $students = DB::table('students')
            ->select(
                'students.id as student_id',
                'income_details.id as income_details_id',
                'students.user_identification',
                'students.name as student_name',
                'students.last_names as student_lastnames',
                'incomes.concept as concept',
                'incomes.total as concept_total',
                'incomes.discount as discount',
                'incomes.quantity as quantity',
                DB::raw('COUNT(incomes.id) as income_count'),
                DB::raw('SUM(incomes.total) as total_incomes')
            )
            // ingresos como (credito, mensualidades)
            ->rightJoin('income_details', 'income_details.student_id', '=', 'students.id')
            ->rightJoin('incomes', 'income_details.id', '=', 'incomes.income_details_id')

            // ingreso como (products)
            ->groupBy(
                'students.id',
                'students.user_identification',
                'students.name',
                'students.last_names',
                'income_details.id',
                'incomes.concept',
                'incomes.total',
                'incomes.discount',
                'incomes.quantity'
            )
            ->orderBy('income_details.payment_date', 'desc')
            ->get();

        $studentData = [];

        foreach ($students as $student) {
            if (!isset($studentData[$student->student_id])) {
                $studentData[$student->student_id] = [
                    'id_student' => $student->student_id,
                    'user_identification' => $student->user_identification,
                    'student_name' => $student->student_name,
                    'student_lastnames' => $student->student_lastnames,
                    'income_count' => 0,
                    'total_incomes' => 0,
                    'incomes' => []
                ];
            }

            $income = IncomeDetails::with('employee')->find($student->income_details_id);

            if ($income && $income->employee) {
                $studentData[$student->student_id]['incomes'][] = [
                    'id' => $income->id,
                    'employee_name' => $income->employee->name,
                    'student_id' => $income->student_id,
                    'commission' => $income->commission,
                    'payment_method' => $income->payment_method,
                    'bank_account' => $income->bank_account,
                    'file_path' => $income->file_path,
                    'ticket_path' => $income->ticket_path,
                    'total' => $income->total,
                    'payment_date' => $income->payment_date,
                    'created_at' => $income->created_at,
                    'updated_at' => $income->updated_at,
                    'concept' => $student->concept,
                    'concept_total' => $student->concept_total,
                    'discount' => $student->discount,
                    'quantity' => $student->quantity
                ];
            }

            $studentData[$student->student_id]['income_count'] += $student->income_count;
            $studentData[$student->student_id]['total_incomes'] += $student->total_incomes;
        }

        $studentData = array_values($studentData);

        return response()->json(['students' => $studentData], 200);
    }


    /*
     * paylod :
     * {
     *      'id_income': string,
     *      'date': string
     * }
     */

    public function incomeModifyDate(Request $request)
    {
        // Validación de los datos recibidos
        $validator = Validator::make($request->all(), [
            'id_income' => 'required',
            'date' => 'required|date'  // Se agrega validación de formato de fecha
        ], [
            'id_income.required' => 'El id del ingreso es requerido',
            'date.required' => 'La nueva fecha es requerida',
        ]);

        if ($validator->fails()) {
            return response()->json(['resp' => $validator->errors()], 400);
        }

        // Encuentra el ingreso por su ID
        $income = Income::find($request->id_income);

        if (!$income) {
            return response()->json(['resp' => 'Ingreso no existente'], 404);
        }

        // Accede a los detalles del ingreso a través de la relación
        $incomeDetails = $income->incomeDetails;

        if (!$incomeDetails) {
            return response()->json(['resp' => 'Detalles del ingreso no encontrados'], 404);
        }

        // Modifica el campo payment_date
        $incomeDetails->payment_date = $request->date;



        // Guarda los cambios en el modelo de IncomeDetails
        $incomeDetails->save();

        // Devuelve la respuesta con el modelo actualizado
        return response()->json(['resp' => 'Se modificó correctamente el ingreso', 'data' => $incomeDetails], 200);
    }

    public function getIncomesByStudentId($studentId) {
        // Asegúrate de que el ID del estudiante sea un número
        if (!is_numeric($studentId)) {
            return response()->json(['error' => 'Invalid student ID'], 400);
        }

        // Consulta los ingresos del estudiante por ID
        $incomes = DB::table('incomes')
            ->select(
                'incomes.id',
                'employees.name as employee_name',
                'income_details.student_id',
                'income_details.commission',
                'income_details.payment_method',
                'income_details.bank_account',
                'income_details.file_path',
                'income_details.ticket_path',
                'income_details.total',
                'income_details.payment_date',
                'incomes.concept',
                'incomes.discount',
                'incomes.quantity'
            )
            ->join('income_details', 'incomes.income_details_id', '=', 'income_details.id')
            ->join('employees', 'income_details.employee_id', '=', 'employees.id')
            ->where('income_details.student_id', $studentId)
            ->get();

        // Si no se encuentran ingresos, devolver un mensaje
        if ($incomes->isEmpty()) {
            return response()->json(['message' => 'No incomes found for this student'], 404);
        }

        // Combinar ingresos duplicados
        $groupedIncomes = [];
        foreach ($incomes as $income) {
            $key = $income->payment_method . '|' . $income->ticket_path . '|' . $income->total . '|' . $income->payment_date;

            if (isset($groupedIncomes[$key])) {
                // Si el ingreso ya existe, agregar el concepto al arreglo
                if (is_array($groupedIncomes[$key]->concept)) {
                    $groupedIncomes[$key]->concept[] = $income->concept;
                } else {
                    $groupedIncomes[$key]->concept = [$groupedIncomes[$key]->concept, $income->concept];
                }
            } else {
                // Si no existe, agregar el ingreso al arreglo agrupado
                $groupedIncomes[$key] = clone $income;
            }
        }

        // Reindexar los ingresos agrupados
        $result = array_values($groupedIncomes);

        return response()->json($result, 200);
    }
}
