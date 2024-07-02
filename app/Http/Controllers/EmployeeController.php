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
            ->select('employees.id', 'employees.name', 'employees.last_names', 'employees.user_identification', 'employees.user_type','bases.name as base')
            ->orderBy('employees.id', 'desc')
            ->get();

        return response()->json(['employees' => $employees]);
    }
}
