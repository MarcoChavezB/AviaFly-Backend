<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request){

        $employees = DB::table('employees')
            ->join('bases', 'employees.id_base', '=', 'bases.id')
            ->select('employees.id', 'employees.name', 'employees.last_names', 'employees.user_identification', 'employees.email', 'employees.phone', 'employees.cellphone', 'employees.company_email', 'employees.user_type','bases.name as base')
            ->get();

        if($employees->isEmpty()){
            return response()->json([
                'message' => 'No hay empleados registrados'
            ], 404);
        }

        return response()->json(['employees' => $employees]);
    }
}
