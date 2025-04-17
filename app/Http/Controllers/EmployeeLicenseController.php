<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLicense;

class EmployeeLicenseController extends Controller
{
    public function index()
    {
        $licenses = EmployeeLicense::with(['employee', 'license' => function ($query) {
            $query->withTrashed();
        }])->get();

        if($licenses->isEmpty()) {
            return response()->json([
                'message' => 'No hay licencias disponibles',
                'successfully' => false
            ], 404);
        }

        // Agrupar por empleado
        $grouped = $licenses->groupBy('employee.id')->map(function ($group) {
            $employee = $group->first()->employee;

            return [
                'employee' => [
                    'id' => $employee->user_identification,
                    'name' => $employee->name,
                    'last_names' => $employee->last_names,
                    'cellphone' => $employee->cellphone,
                    'email' => $employee->email,
                ],
                'licenses' => $group->map(function ($item) {
                    return [
                        'id' => $item->license->id ?? null,
                        'license_name' => $item->license->name ?? null,
                        'expiration_date' => $item->expiration_date,
                        'license_date' => $item->license_date,
                    ];
                })->values()
            ];
        })->values(); // quita las claves de grupo

        return response()->json([
            'data' => $grouped,
            'message' => 'Licencias agrupadas por empleado',
            'successfully' => true,
            'total_empleados' => $grouped->count()
        ]);
    }
}
