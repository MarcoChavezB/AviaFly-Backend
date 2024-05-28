<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    public function getSubjects()
    {
        $subjects = Subject::get(['id', 'name', 'weeks_duration']);

        if($subjects->isEmpty()){
            return response()->json(["errors" => ["No hay materias creadas"]], 404);
        }

        return response()->json($subjects, 200);

    }

}
