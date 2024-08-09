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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'career_id' => 'required|integer|exists:careers,id',
                'instructor_id' => 'required|integer|exists:employees,id',
                'turn_id' => 'required|integer|exists:turns,id',
            ],
                [
                    'name.required' => 'El nombre de la materia es requerido',
                    'name.string' => 'El nombre de la materia debe ser una cadena de texto',
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
            $teacher_subject_turn->career_subject_id = $career_subject->id;
            $teacher_subject_turn->id_turn = $request->turn_id;
            $teacher_subject_turn->save();

            return response()->json($teacher_subject_turn, 201);
        }catch (\Exception $e){
            return response()->json(['msg' => 'Internal Server Error'], 500);
        }
    }

    public function destroy(Request $request){
        try{
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

            DB::transaction(function () use ($request) {
                $teacher_subject_turn = TeacherSubjectTurn::where('id', $request->teacher_sub_turn_id)->first();
                if(!$teacher_subject_turn){
                    return response()->json(["errors" => ["La materia no esta relacionada con ningun instructor."]], 400);
                }
                $teacher_subject_turn->delete();

                $career_subject = CareerSubject::where('id', $request->career_sub_id)->first();
                if(!$career_subject){
                    return response()->json(["errors" => ["La materia no esta relacionada con ninguna carrera."]], 400);
                }
                $career_subject->delete();
            });

            return response()->json(["message" => "Materia eliminada"], 200);
        }catch (\Exception $e) {
            return response()->json(['msg' => 'Internal Server Error'], 500);
        }
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

    public function getSubjectsEndingSoon(){ //para administrador y root

        $today = date('Y-m-d');
        $oneDayFromNow = date('Y-m-d', strtotime($today . ' + 1 days'));
        $twoDaysFromNow = date('Y-m-d', strtotime($today . ' + 2 days'));

        $subjects = DB::table('teacher_subject_turns')
            ->join('career_subjects', 'teacher_subject_turns.career_subject_id', '=', 'career_subjects.id')
            ->join('subjects', 'career_subjects.id_subject', '=', 'subjects.id')
            ->join('careers', 'career_subjects.id_career', '=', 'careers.id')
            ->join('employees', 'teacher_subject_turns.id_teacher', '=', 'employees.id')
            ->join('bases', 'employees.id_base', '=', 'bases.id')
            ->join('turns', 'teacher_subject_turns.id_turn', '=', 'turns.id')
            ->select('subjects.name', 'bases.name as base', 'teacher_subject_turns.end_date', 'turns.name as turn', 'careers.name as career')
            ->whereIn('teacher_subject_turns.end_date', [$today, $oneDayFromNow, $twoDaysFromNow])
            ->get();

        $formattedSubjects = $subjects->map(function ($subject) use ($today) {
            $daysLeft = (strtotime($subject->end_date) - strtotime($today)) / (60 * 60 * 24);
            $status = $daysLeft >= 0 ? $daysLeft . ' dia(s)' : 'Expirada';
            return [
                'name' => $subject->name,
                'base' => $subject->base,
                'daysToExpire' => $status,
                'turn' => $subject->turn,
                'career' => $subject->career
            ];
        });

        return response()->json(['subjects' => $formattedSubjects], 200);
    }

}
