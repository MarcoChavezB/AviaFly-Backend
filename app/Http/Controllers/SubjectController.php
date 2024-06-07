<?php

namespace App\Http\Controllers;

use App\Models\CareerSubject;
use App\Models\Subject;
use App\Models\TeacherSubjectTurn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class SubjectController extends Controller
{

    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'duration' => 'required|integer',
            'career_id' => 'required|integer|exists:careers,id',
            'instructor_id' => 'required|integer|exists:employees,id',
            'turn_id' => 'required|integer|exists:turns,id',
        ],
        [
            'name.required' => 'El nombre de la materia es requerido',
            'name.string' => 'El nombre de la materia debe ser una cadena de texto',
            'start_date.required' => 'La fecha de inicio es requerida',
            'start_date.date' => 'La fecha de inicio debe ser una fecha',
            'end_date.required' => 'La fecha de fin es requerida',
            'end_date.date' => 'La fecha de fin debe ser una fecha',
            'duration.required' => 'La duración es requerida',
            'duration.integer' => 'La duración debe ser un número entero',
            'career_id.required' => 'El id de la carrera es requerido',
            'career_id.integer' => 'El id de la carrera debe ser un número entero',
            'career_id.exists' => 'El id de la carrera no existe',
            'instructor_id.required' => 'El id del instructor es requerido',
            'instructor_id.integer' => 'El id del instructor debe ser un número entero',
            'instructor_id.exists' => 'El id del instructor no existe',
            'turn_id.required' => 'El id del turno es requerido',
            'turn_id.integer' => 'El id del turno debe ser un número entero',
            'turn_id.exists' => 'El id del turno no existe',
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $subject = new Subject();
        $subject->name = $request->name;
        $subject->save();

        $career_subject = new CareerSubject();
        $career_subject->id_career = $request->career_id;
        $career_subject->id_subject = $subject->id;
        $career_subject->save();

        $teacher_subject_turn = new TeacherSubjectTurn();
        $teacher_subject_turn->id_teacher = $request->instructor_id;
        $teacher_subject_turn->id_subject = $subject->id;
        $teacher_subject_turn->id_turn = $request->turn_id;
        $teacher_subject_turn->start_date = $request->start_date;
        $teacher_subject_turn->end_date = $request->end_date;
        $teacher_subject_turn->duration = $request->duration;
        $teacher_subject_turn->save();

        return response()->json($teacher_subject_turn, 201);

    }

    public function destroy(Request $request){

        $validator = Validator::make($request->all(),[
            'career_sub_id' => 'required|integer|exists:career_subjects,id',
            'teacher_sub_turn_id' => 'required|integer|exists:teacher_subject_turns,id',
        ],
            [
                'career_sub_id.required' => 'El id de la materia es requerido',
                'career_sub_id.integer' => 'El id de la materia debe ser un número entero',
                'career_sub_id.exists' => 'El id de la materia no existe',
                'teacher_sub_turn_id.required' => 'El id de la carrera es requerido',
                'teacher_sub_turn_id.integer' => 'El id de la carrera debe ser un número entero',
                'teacher_sub_turn_id.exists' => 'El id de la carrera no existe',
            ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $career_subject = CareerSubject::where('id', $request->career_sub_id)
            ->first();
        if(!$career_subject){
            return response()->json(["errors" => ["La materia no esta relacionada con ninguna carrera"]], 400);
        }
        $career_subject->delete();

        $teacher_subject_turn = TeacherSubjectTurn::where('id', $request->teacher_sub_turn_id)->first();
        if(!$teacher_subject_turn){
            return response()->json(["errors" => ["La materia no esta relacionada con ningun instructor, pero fue eliminada correctamente"]], 400);
        }
        $teacher_subject_turn->delete();


        return response()->json(["message" => "Materia eliminada"], 200);
    }

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
