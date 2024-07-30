<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\StudentSubject;
use App\Models\TeacherSubjectTurn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Base;

class InstructorController extends Controller
{

    public function index(){
        $employees = Employee::where('user_type', 'flight_instructor')->get();
        return response()->json($employees, 200);
    }

    public function create(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'last_names' => 'required|string',
                'email' => 'required|email|unique:employees,email',
                'company_email' => 'required|email|unique:employees,company_email',
                'phone' => 'required|string',
                'cellphone' => 'required|string',
                'base' => 'required|exists:bases,id',
                'curp' => 'required|string|unique:employees,curp',
                'user_type' => 'required|string|in:instructor,employee,admin'            ],
                [
                    'name.required' => 'El nombre es requerido',
                    'name.string' => 'El nombre no es válido',
                    'last_names.required' => 'El apellido es requerido',
                    'last_names.string' => 'El apellido no es válido',
                    'email.required' => 'El correo electrónico es requerido',
                    'email.email' => 'El correo electrónico no es válido',
                    'email.unique' => 'El correo electrónico ya está en uso',
                    'company_email.required' => 'El correo electrónico de la empresa es requerido',
                    'company_email.unique' => 'El correo electrónico de la empresa ya está en uso',
                    'company_email.email' => 'El correo electrónico de la empresa no es válido',
                    'phone.required' => 'El teléfono es requerido',
                    'phone.string' => 'El teléfono no es válido',
                    'cellphone.required' => 'El celular es requerido',
                    'cellphone.string' => 'El celular no es válido',
                    'base.required' => 'La base es requerida',
                    'base.exists' => 'La base no existe',
                    'curp.required' => 'La CURP es requerida',
                    'curp.unique' => 'La CURP ya está en uso',
                    'curp.string' => 'La CURP no es válida',
                    'user_type.required' => 'El tipo de usuario es requerido',
                    'user_type.string' => 'El tipo de usuario no es válido',
                    'user_type.in' => 'El tipo de usuario no es válido'
                ]);

            if($validator->fails()){
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $instructor = new Employee();
            $instructor->name = $request->name;
            $instructor->last_names = $request->last_names;
            $instructor->email = $request->email;
            $instructor->company_email = $request->company_email;
            $instructor->phone = $request->phone;
            $instructor->cellphone = $request->cellphone;
            $instructor->curp = strtoupper( $request->curp);
            $instructor->user_type = $request->user_type;
            $instructor->id_base = $request->base;
            $instructor->save();

            $base = Base::find($request->base);
            $instructor->user_identification = 'E' . strtoupper($instructor->user_type[0]) . $base->name[0] . $instructor->id;
            $instructor->save();

            $user = new User();
            $user->user_identification = $instructor->user_identification;
            $user->password = bcrypt($instructor->curp);
            $user->user_type = $instructor->user_type;
            $user->id_base = $request->base;
            $user->save();

            return response()->json($user->user_identification, 201);
        }catch(\Exception $e){
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function getInstructorCareers(){
        $user = Auth::user();

        $instructor = Employee::where('user_identification', $user->user_identification)->first();

        if($instructor) {
            $careers = DB::table('student_subjects')
                ->join('students', 'student_subjects.id_student', '=', 'students.id')
                ->join('careers', 'students.id_career', '=', 'careers.id')
                ->where('student_subjects.id_teacher', $instructor->id)
                ->select('careers.id', 'careers.name')
                ->distinct()
                ->get();

            if($careers->isEmpty()){
                return response()->json(["errors" => ["No hay formaciones asignadas"]], 404);
            }

            return response()->json(['careers'=>$careers]);
        }

        return response()->json(["error" => "Instructor not found"], 404);
    }

    public function getInstructorSubjects(){
        $user = Auth::user();

        $instructor = Employee::where('user_identification', $user->user_identification)->first();

        if($instructor) {
            $subjects = DB::table('student_subjects')
                ->join('students', 'student_subjects.id_student', '=', 'students.id')
                ->join('subjects', 'student_subjects.id_subject', '=', 'subjects.id')
                ->where('student_subjects.id_teacher', $instructor->id)
                ->select('subjects.id', 'subjects.name')
                ->distinct()
                ->get();

            if($subjects->isEmpty()){
                return response()->json(["errors" => ["No hay materias asignadas"]], 404);
            }

            return response()->json(['subjects'=>$subjects]);
        }

        return response()->json(["error" => "Instructor not found"], 404);
    }

    public function getStudentsByInstructor(Request $request){
        $user = Auth::user();

        $instructor = Employee::where('user_identification', $user->user_identification)->first();

        if($instructor) {
            $query = DB::table('student_subjects')
                ->join('students', 'student_subjects.id_student', '=', 'students.id')
                ->join('careers', 'students.id_career', '=', 'careers.id')
                ->join('employees', 'student_subjects.id_teacher', '=', 'employees.id')
                ->join('subjects', 'student_subjects.id_subject', '=', 'subjects.id')
                ->leftJoin('teacher_subject_turns', function($join) use ($instructor) {
                    $join->on('student_subjects.id_subject', '=', 'teacher_subject_turns.id_subject')
                        ->on('student_subjects.id_turn', '=', 'teacher_subject_turns.id_turn')
                        ->where('employees.id', '=', $instructor->id);
                })
                ->where('student_subjects.id_teacher', $instructor->id)
                ->select('students.id as student_id', 'subjects.name as subject_name','student_subjects.id_subject as subject_id', 'students.user_identification as student_identification', DB::raw('CONCAT(students.name, " ", students.last_names) as student_full_name'),
                    'careers.name as career_name', 'careers.id as career_id',
                    'student_subjects.final_grade', 'student_subjects.id_subject as student_subject_id',
                    'student_subjects.duration', 'student_subjects.start_date', 'student_subjects.end_date', 'student_subjects.updated_at as last_update', 'student_subjects.status as grade_status');


            if($request->has('career_id')) {
                $query->where('careers.id', $request->career_id);
            }

            if($request->has('without_grade')) {
                $query->whereNull('student_subjects.final_grade');
            }

            if($request->has('subject_id')){
                $query->where('student_subjects.id_subject', $request->subject_id);
            }

            $students = $query->get();

            if($students->isEmpty()){
                return response()->json(["errors" => ["No hay estudiantes con los parametros solicitados"]], 404);
            }

            return response()->json(['students'=>$students]);
        }

        return response()->json(["error" => "Instructor not found"], 404);
    }

    public function getInstructorsSubjects(Request $request){

    $validator = Validator::make($request->all(), [
        'career_id' => 'required|exists:careers,id'
    ],
    [
        'career_id.required' => 'La formación es requerida',
        'career_id.exists' => 'La formación no existe'
    ]);

    if($validator->fails()){
        return response()->json(["errors" => $validator->errors()], 400);
    }

    $user = Auth::user();

    $admin = Employee::where('user_identification', $user->user_identification)->first();

    if($admin){

        $teachers_subjects = DB::table('teacher_subject_turns')
            ->join('employees', 'teacher_subject_turns.id_teacher', '=', 'employees.id')
            ->join('career_subjects', 'teacher_subject_turns.career_subject_id', '=', 'career_subjects.id')
            ->join('subjects', 'career_subjects.id_subject', '=', 'subjects.id')
            ->join('turns', 'teacher_subject_turns.id_turn', '=', 'turns.id')
            ->select('teacher_subject_turns.id as id', 'employees.user_identification as teacher_identification', DB::raw('CONCAT(employees.name, " ", employees.last_names) as teacher_full_name'),
                'career_subjects.id as career_subject_id', 'subjects.name as subject_name',
                'turns.name as turn_name', 'teacher_subject_turns.start_date', 'teacher_subject_turns.end_date', 'teacher_subject_turns.duration', 'career_subjects.id as career_subjects_id')
            ->where('employees.id_base', $admin->id_base)
            ->where('career_subjects.id_career', $request->career_id)
            ->get();

        if($teachers_subjects->isEmpty()){
            return response()->json(["errors" => ["No hay materias ni profesores asignados"]], 400);
        }

        $instructors = Employee::where('user_type', 'instructor')->where('id_base', $admin->id_base)->get(['id', 'user_identification', 'name', 'last_names']);

        if($instructors->isEmpty()){
            return response()->json(["errors" => ["No hay instructores asignados"]], 400);
        }

        $turns = DB::table('turns')->get(['id', 'name']);

        if($turns->isEmpty()){
            return response()->json(["errors" => ["No hay turnos asignados"]], 400);
        }

        return response()->json(['teachers_subjects'=>$teachers_subjects, 'turns'=>$turns, 'instructors'=>$instructors]);
    }

    return response()->json(["error" => "Admin not found"], 404);
}

    public function updateInstructorsSubjects(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:teacher_subject_turns,id',
            'teacher_id' => 'sometimes|exists:employees,id',
            'turn_id' => 'sometimes|exists:turns,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'duration' => 'sometimes|numeric'
        ],
        [
            'id.required' => 'La materia es requerida',
            'id.exists' => 'La materia no existe',
            'teacher_id.exists' => 'El profesor no existe',
            'turn_id.exists' => 'El turno no existe',
            'start_date.date' => 'La fecha de inicio no es válida',
            'end_date.date' => 'La fecha de fin no es válida',
            'duration.numeric' => 'La duración no es válida'
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors(), 'id from req' => $request->id], 400);
        }

        $teacher_subject = TeacherSubjectTurn::find($request->id);

        if($teacher_subject){
            if($request->has('teacher_id')){
                $teacher_subject->id_teacher = $request->id;
            }
            if($request->has('turn_id')){
                $teacher_subject->id_turn = $request->turn_id;
            }
            if($request->has('start_date')){
                $teacher_subject->start_date = $request->start_date;
            }
            if($request->has('end_date')){
                $teacher_subject->end_date = $request->end_date;
            }
            if($request->has('duration')){
                $teacher_subject->duration = $request->duration;
            }

            $teacher_subject->save();

            return response()->json(['msg' => 'ok'], 200);
        }

        return response()->json(["errors" => ["No se encontro la relacion de la materia con el profesor"]], 404);
    }

    public function updateStudentGrade(Request $request){
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
            'grade' => 'required|numeric|min:0|max:100'
        ],
        [
            'subject_id.required' => 'La materia es requerida',
            'subject_id.exists' => 'La materia no existe',
            'grade.required' => 'La calificación es requerida',
            'grade.numeric' => 'La calificación no es válida',
            'grade.min' => 'La calificación no puede ser menor a 0',
            'grade.max' => 'La calificación no puede ser mayor a 100',
            'student_id.required' => 'El estudiante es requerido',
            'student_id.exists' => 'El estudiante no existe'
        ]);

        if($validator->fails()){
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $studentSubject = StudentSubject::where('id_student', $request->student_id)
            ->where('id_subject', $request->subject_id)
            ->first();

        if($studentSubject){
            $studentSubject->final_grade = $request->grade;
            $studentSubject->status = $request->grade >= 85 ? 'approved' : 'failed';
            $studentSubject->save();

            return response()->json($studentSubject, 200);
        }

        return response()->json(["errors" => ["No se encontro la relación de la materia con el estudiante"]], 404);
    }

    public function getInstructorsAndTurns(){
        $user = Auth::user();
        $admin = Employee::where('user_identification', $user->user_identification)->first();

        $instructors = Employee::where('user_type', 'instructor')->where('id_base', $admin->id_base)->get(['id', 'user_identification', 'name', 'last_names']);

        if($instructors->isEmpty()){
            return response()->json(["errors" => ["No hay instructores asignados"]], 400);
        }

        $turns = DB::table('turns')->get(['id', 'name']);

        if($turns->isEmpty()){
            return response()->json(["errors" => ["No hay turnos asignados"]], 400);
        }

        return response()->json(['turns'=>$turns, 'instructors'=>$instructors]);
    }
}
