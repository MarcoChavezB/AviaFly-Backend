<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Base;

class InstructorController extends Controller
{
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
            $instructor->user_identification = strtoupper($instructor->user_type[0]) . $base->name[0] . $instructor->id;
            $instructor->save();

            $user = new User();
            $user->user_identification = $instructor->user_identification;
            $user->password = bcrypt($instructor->curp);
            $user->user_type = $instructor->user_type;
            $user->save();

            return response()->json($instructor->user_identification, 201);
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

    public function getStudentsByInstructor(Request $request){
        $user = Auth::user();

        $instructor = Employee::where('user_identification', $user->user_identification)->first();

        if($instructor) {
            $query = DB::table('student_subjects')
                ->join('students', 'student_subjects.id_student', '=', 'students.id')
                ->join('careers', 'students.id_career', '=', 'careers.id')
                ->join('teacher_subject_turns', function($join) use ($instructor) {
                    $join->on('student_subjects.id_subject', '=', 'teacher_subject_turns.id_subject')
                        ->on('student_subjects.id_turn', '=', 'teacher_subject_turns.id_turn')
                        ->where('teacher_subject_turns.id_teacher', '=', $instructor->id);
                })
                ->select('students.id as student_id', 'students.user_identification as student_identification', DB::raw('CONCAT(students.name, " ", students.last_names) as student_full_name'),
                    'careers.name as career_name', 'careers.id as career_id',
                    'student_subjects.final_grade', 'student_subjects.id_subject as student_subject_id',
                    'student_subjects.duration', 'student_subjects.start_date', 'student_subjects.end_date', 'student_subjects.updated_at as last_update', 'student_subjects.status as grade_status');


            if($request->has('career_id')) {
                $query->where('careers.id', $request->career_id);
            }

            if($request->has('without_grade')) {
                $query->whereNull('student_subjects.final_grade');
            }

            $students = $query->get();

            if($students->isEmpty()){
                return response()->json(["errors" => ["No hay estudiantes con los parametros solicitados"]], 404);
            }

            return response()->json(['students'=>$students]);
        }

        return response()->json(["error" => "Instructor not found"], 404);
    }
}
