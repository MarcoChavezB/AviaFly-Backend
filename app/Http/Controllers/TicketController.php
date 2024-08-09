<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\IncomeDetails;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\FileController;
use App\Models\Employee;

class TicketController extends Controller
{
    public function generateTicket(array $data, string $employeeName, string $employeeLastNames, int $employeeBaseId, int $incomeDetailsId, Student $student, Employee $employee, int $id_payment): string
    {

        $fileController = new FileController();
        $baseData = Base::findOrFail($employeeBaseId);
        $incomeDetails = IncomeDetails::findOrFail($incomeDetailsId);

        $pdf =
            Pdf::loadView
            ('income_ticket',
            compact
            (
            'data',
            'student',
            'employeeName',
            'employeeLastNames',
            'baseData',
            'incomeDetails'
            ));

        $fileUrl = $fileController->saveTicket(
            $pdf->output(),
            $student,
            $employee->id_base
        );

        return response()->json([
            'urlTiket' => $fileUrl,
        ]);
    }
}
