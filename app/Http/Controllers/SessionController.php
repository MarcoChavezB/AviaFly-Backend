<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\StageSession;
use App\Models\studentLesson;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function syllabus(int $id_student)
    {
        $sessions = studentLesson::select(
            "students.user_identification as student_identification",
            "students.id as id_student",
            "students.name as student_name",
            "stages.name as stage_name",
            "sessions.name as session_name",
            "sessions.session_objetive",
            "sessions.approvation_standard",
            "lessons.name as lesson_name",
            "student_lessons.passed as lesson_passed",
            "flight_objetives.name as flight_objetive_name",
            "flight_history.type_flight"
        )->join('students', 'students.id', '=', 'student_lessons.id_student')
        ->join('lessons', 'lessons.id', '=', 'student_lessons.id_lesson')
        ->join('lesson_objetive_sessions', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
        ->join('sessions', 'sessions.id', '=', 'lesson_objetive_sessions.id_session')
        ->join('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
        ->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
        ->join('flight_objetives', 'flight_objetives.id', '=', 'lesson_objetive_sessions.id_flight_objetive')
        ->join('flight_payments', 'flight_payments.id_student', '=', 'students.id')
        ->join('flight_history', 'flight_history.id', '=', 'flight_payments.id_flight')
        ->where('students.id', $id_student)
        ->groupBy('students.name', 'stages.name', 'sessions.name', 'lessons.name', 'student_lessons.passed', 'flight_objetives.name', 'students.user_identification', 'students.id', 'flight_history.type_flight', 'sessions.session_objetive', 'sessions.approvation_standard');

        $sessions = $sessions->get()->sortBy(function ($session) {
            preg_match('/\d+/', $session->session_name, $matches);
            return (int) $matches[0];
        });

        // Reorganizar los resultados
        $result = [];
        foreach ($sessions as $session) {
            $studentIdentification = $session->student_identification;
            $studentId = $session->id_student;
            $studentName = $session->student_name;
            $stageName = $session->stage_name;
            $sessionName = $session->session_name;
            $sessionObjetive = $session->session_objetive;
            $sessionApprovationStandard = $session->approvation_standard;
            $flightObjetiveName = $session->flight_objetive_name;
            $lessonName = $session->lesson_name;
            $lessonPassed = $session->lesson_passed;
            $flightType = $session->type_flight;

            if (!isset($result[$studentName])) {
                $result[$studentName] = [
                    'student_identification' => $studentIdentification,
                    'id_student' => $studentId,
                    'student_name' => $studentName,
                    'flight_type' => $flightType,
                    'stages' => []
                ];
            }

            if (!isset($result[$studentName]['stages'][$stageName])) {
                $result[$studentName]['stages'][$stageName] = [
                    'stage_name' => $stageName,
                    'sessions' => []
                ];
            }

            if (!isset($result[$studentName]['stages'][$stageName]['sessions'][$sessionName])) {
                $result[$studentName]['stages'][$stageName]['sessions'][$sessionName] = [
                    'session_objetive' => $sessionObjetive,
                    'approvation_standard' => $sessionApprovationStandard,
                    'session_name' => $sessionName,
                    'flight_objetive' => []
                ];
            }

            if (!isset($result[$studentName]['stages'][$stageName]['sessions'][$sessionName]['flight_objetive'][$flightObjetiveName])) {
                $result[$studentName]['stages'][$stageName]['sessions'][$sessionName]['flight_objetive'][$flightObjetiveName] = [
                    'flight_objetive_name' => $flightObjetiveName,
                    'lessons' => []
                ];
            }

            $result[$studentName]['stages'][$stageName]['sessions'][$sessionName]['flight_objetive'][$flightObjetiveName]['lessons'][] = [
                'lesson_name' => $lessonName,
                'lesson_passed' => $lessonPassed
            ];
        }

        // Convertir el resultado a un array indexado
        $finalResult = [];
        foreach ($result as $student) {
            $stages = [];
            foreach ($student['stages'] as $stage) {
                $sessions = [];
                foreach ($stage['sessions'] as $session) {
                    $flightObjectives = [];
                    foreach ($session['flight_objetive'] as $flightObjective) {
                        $flightObjectives[] = $flightObjective;
                    }
                    $session['flight_objetive'] = $flightObjectives;
                    $sessions[] = $session;
                }
                $stage['sessions'] = $sessions;
                $stages[] = $stage;
            }
            $student['stages'] = $stages;
            $finalResult[] = $student;
        }

        return response()->json($finalResult);
    }

    public function showLessons($is_student){
        $sessions = StageSession::select(
            "sessions.id as session_id",
            "stages.name as stage_name",
            "sessions.name as session_name",
            "student_lessons.passed as session_passed"
        )->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
        ->join('sessions', 'sessions.id', '=', 'stage_sessions.id_session')
        ->join('lesson_objetive_sessions', 'lesson_objetive_sessions.id_session', '=', 'sessions.id')
        ->join('lessons', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
        ->join('student_lessons', 'student_lessons.id_lesson', '=', 'lessons.id')
        ->where('student_lessons.id_student', $is_student)
        ->groupBy('stages.name', 'sessions.name', 'student_lessons.passed', 'sessions.id');

        $sessions = $sessions->get()->sortBy(function ($session) {
            preg_match('/\d+/', $session->session_name, $matches);
            return (int) $matches[0];
        });

        $stages = [];

        foreach ($sessions as $session) {
            $sessionId = $session->session_id;
            $stageName = $session->stage_name;
            $sessionName = $session->session_name;
            $sessionPassed = $session->session_passed;

            if (!isset($stages[$stageName])) {
                $stages[$stageName] = [
                    'stage_name' => $stageName,
                    'sessions' => []
                ];
            }

            $stages[$stageName]['sessions'][] = [
                'session_id' => $sessionId,
                'session_name' => $sessionName,
                'session_passed' => $sessionPassed
            ];
        }

        // Extraer solo las etapas y convertirlo en un array
        $result = array_values($stages);

        return response()->json($result);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function show(Session $session)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function edit(Session $session)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Session $session)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function destroy(Session $session)
    {
        //
    }
}
