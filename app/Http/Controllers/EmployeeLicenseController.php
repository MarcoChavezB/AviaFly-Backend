<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
                        'group' => $item->group,
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @Body (
     *   {
            "employee_userIdentification": EIP3,
            "licenses": [
                {
                    "id": number,
                    "name": string,
                    "start_date": string,
                    "end_date": string,
                },
            ]
        }
     * )
     */
    public function putLicenseToEmployee(Request $request){

        $validator = Validator::make($request->all(), [
            'employee_userIdentification' => 'required|exists:employees,user_identification',
            'licenses' => 'required|array',
            'licenses.*.name' => 'required|string',
            'licenses.*.start_date' => 'required|date',
            'licenses.*.end_date' => 'required|date',
        ], [
            'employee_userIdentification.required' => 'El campo user_identification es obligatorio.',
            'employee_userIdentification.exists' => 'El empleado no existe.',
            'licenses.required' => 'El campo licenses es obligatorio.',
            'licenses.array' => 'El campo licenses debe ser un arreglo.',
            'licenses.*.name.required' => 'El campo name es obligatorio.',
            'licenses.*.name.string' => 'El campo name debe ser una cadena de texto.',
            'licenses.*.start_date.required' => 'El campo start_date es obligatorio.',
            'licenses.*.start_date.date' => 'El campo start_date debe ser una fecha válida.',
            'licenses.*.end_date.required' => 'El campo end_date es obligatorio.',
            'licenses.*.end_date.date' => 'El campo end_date debe ser una fecha válida.',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'successfully' => false
            ], 422);
        }

        $employee = Employee::where('user_identification', $request->employee_userIdentification)->first();

        if(!$employee){
            return response()->json([
                'message' => 'Empleado no encontrado',
                'successfully' => false
            ], 404);
        }
        $licenses = $request->licenses;

        if(empty($licenses)){
            return response()->json([
                'message' => 'No hay licencias para asignar',
                'successfully' => false
            ], 404);
        }

        DB::transaction(function () use ($licenses, $employee){
            //NOTE: Obtener el grupo de la licencia por peticion a API
            $maxNumber = EmployeeLicense::where('id_employee', $employee->id)
                ->lockForUpdate()
                ->max('group');

            $group = ($maxNumber ?? 0) + 1;

            foreach($licenses as $license){
                // Verificar si la licencia ya existe para el empleado
                $existingLicense = EmployeeLicense::where('id_employee', $employee->id)
                    ->where('id_license', $license['id'])
                    ->first();

                if ($existingLicense) {
                    // Si la licencia ya existe, actualizarla
                    $existingLicense->update([
                        'expiration_date' => $license['end_date'],
                        'license_date' => $license['start_date'],
                    ]);
                    continue;
                }


                EmployeeLicense::create([
                    'id_employee' => $employee->id,
                    'id_license' => $license['id'],
                    'expiration_date' => $license['end_date'],
                    'license_date' => $license['start_date'],
                    'group' => $group,
                ]);
            }
        });

        return response()->json([
            'message' => 'Licencias asignadas correctamente',
            'successfully' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @Body (
     *  {
            "id_employee": number,
            "id_license": number,
        }
     */

    public function destroy(Request $request){
        $validator = Validator::make([
            'id_employee' => $request->id_employee,
            'id_license' => $request->id_license,
        ], [
            'id_employee' => 'required|exists:employees,user_identification',
            'id_license' => 'required|exists:licenses,id',
        ], [
            'id_employee.required' => 'El campo id_employee es obligatorio.',
            'id_employee.exists' => 'El empleado no existe.',
            'id_license.required' => 'El campo id_license es obligatorio.',
            'id_license.exists' => 'La licencia no existe.',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'successfully' => false
            ], 422);
        }

        $employee = Employee::where('user_identification', $request->id_employee)->first();

        $employeeLicense = EmployeeLicense::where('id_employee', $employee->id)
            ->where('id_license', $request->id_license)
            ->first();

        if(!$employeeLicense){
            return response()->json([
                'message' => 'Licencia no encontrada para el empleado',
                'successfully' => false
            ], 404);
        }

        $employeeLicense->delete();
        return response()->json([
            'message' => 'Licencia eliminada correctamente',
            'successfully' => true
        ]);
    }
}































