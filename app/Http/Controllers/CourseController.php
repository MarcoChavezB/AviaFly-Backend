<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
   public function create(Request $request){
       $validator = Validator::make($request->all(), [
           'id_subject' => 'required|exists:subjects,id',
           'id_teacher' => 'required|exists:users,id',
           'start_date' => 'required|date',
           'finish_date' => 'required|date',
           'students' => 'required|array',
       ],
       [
           'id_subject.required' => 'La materia es requerida',
           'id_subject.exists' => 'La materia no existe',
           'id_teacher.required' => 'El profesor es requerido',
           'id_teacher.exists' => 'El profesor no existe',
           'start_date.required' => 'La fecha de inicio es requerida',
           'start_date.date' => 'La fecha de inicio no es válida',
           'finish_date.required' => 'La fecha de finalización es requerida',
           'finish_date.date' => 'La fecha de finalización no es válida',
           'students.required' => 'Los estudiantes son requeridos',
           'students.array' => 'Los estudiantes no son válidos',
       ]);

       if ($validator->fails()) {
           return response()->json(["errors" => $validator->errors()], 400);
       }

       $course = new Course();
       $course->id_subject = $request->id_subject;
       $course->id_teacher = $request->id_teacher;
       $course->start_date = $request->start_date;
       $course->finish_date = $request->finish_date;
       $course->save();

         foreach ($request->students as $student) {
             $course_student = new CourseStudent();
             $course_student->id_course = $course->id;
              $course_student->id_student = $student;
              $course_student->save();
         }

         return response()->json($course, 201);
   }
}
