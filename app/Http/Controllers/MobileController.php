<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\NewSletter;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller
{
    public function getTeacherData(){
        $teacher = Auth::user();
    }
}
