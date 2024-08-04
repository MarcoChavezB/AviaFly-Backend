<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\IncomeDetails;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\FileController;
use App\Models\Employee;
use App\Models\Payments;

class TicketController extends Controller
{

    private $fileController;

    public function __construct(FileController $fileController)
    {
        $this->fileController = $fileController;
    }

    public function generateTicket(array $data, string $employeeName, string $employeeLastNames, int $employeeBaseId, int $incomeDetailsId, Student $student, Employee $employee, int $id_payment): string
    {
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

        $fileUrl = $this->fileController->saveTicket(
            $pdf->output(),
            $student,
            $employee->id_base
        );

        return response()->json([
            'urlTiket' => $fileUrl,
        ]);
    }
}
