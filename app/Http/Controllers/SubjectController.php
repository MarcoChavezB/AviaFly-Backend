<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\TeacherSubjectTurn;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    public function getSubjects()
    {
        $subjects = Subject::get(['id', 'name']);

        if($subjects->isEmpty()){
            return response()->json(["errors" => ["No hay materias creadas"]], 404);
        }

        return response()->json($subjects, 200);

    }
    
    function getSubjectsInfoCalendar(int $id_career){
        $results = TeacherSubjectTurn::select('subjects.id', 'subjects.name as title', 'teacher_subject_turns.start_date as start', 'teacher_subject_turns.end_date as end')
        ->leftJoin('subjects', 'subjects.id', '=', 'teacher_subject_turns.id_subject')
        ->leftJoin('career_subjects', 'career_subjects.id_subject', '=', 'subjects.id')
        ->leftJoin('careers', 'careers.id', '=', 'career_subjects.id_career')
        ->where('careers.id', $id_career)
        ->get();
        
        return response()->json($results, 200);
    }
    
}


/**
    {
        "id": 1,
        "name": "Matemática",
        "start_date": "2020-01-01",
        "end_date": "2020-01-31",
    }
*/
