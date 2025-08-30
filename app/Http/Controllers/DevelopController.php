<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\IncomeDetails;
use App\Models\FileController;
use Barryvdh\DomPDF\Facade\Pdf;


class DevelopController extends Controller
{
    public function restoreTickets()
    {
$incomeDetailsList = IncomeDetails::with(['student', 'employee', 'incomes'])->get();

foreach ($incomeDetailsList as $incomeDetails) {
    if ($incomeDetails->ticket_path) {
        continue; // ya tiene ticket, lo saltamos
    }

    $student = $incomeDetails->student;
    $employee = $incomeDetails->employee;
    $base = Base::findOrFail($employee->id_base);

    $data = $incomeDetails->incomes->map(function ($i) {
        return [
            'concept' => $i->concept,
            'quantity' => $i->quantity,
            'original_import' => $i->original_import,
            'discount' => $i->discount,
            'iva' => $i->iva,
            'total' => $i->total,
        ];
    })->toArray();

    $pdf = PDF::loadView('income_ticket', [
        'date' => $incomeDetails->payment_date,
        'data' => $data,
        'studentData' => $student,
        'employeeName' => $employee->name,
        'employeeLastNames' => $employee->last_names,
        'baseData' => $base,
        'incomeDetails' => $incomeDetails,
        'hasExtraHour' => false,
        'uniforms' => [],
    ]);

    $fileController = new FileController();
    $baseName = $this->sanitizeName($base->name);
    $fileName = $this->generateFileName($baseName, $student->user_identification, 'tickets');
    $pdf->save(public_path($fileName));

    $incomeDetails->update([
        'ticket_path' => $fileController->generateManualUrl($fileName),
    ]);
}

        return response()->json(['message' => 'Tickets restaurados con éxito']);
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
}
