<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\NewSletter;
use App\Models\Student;
use App\Models\StudentSubject;
use App\Models\TeacherSubjectTurn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller
{
    public function getTeacherData(){
        $teacher = Auth::user();

        $subjects = TeacherSubjectTurn::with('subject')
            ->where('id_teacher', $teacher->id)
            ->get();

        $students = StudentSubject::with('student')
            ->where('id_teacher', $teacher->id)
            ->get();

        $data = [
            'subjects' => $subjects,
            'students' => $students
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
