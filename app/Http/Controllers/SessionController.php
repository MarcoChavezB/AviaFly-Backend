<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\Session;
use App\Models\StageSession;
use App\Models\studentLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function syllabus(int $id_student)
    {
        $sessionsCountQuery = studentLesson::select(
            'stages.name as stage_name',
            DB::raw('COUNT(DISTINCT sessions.id) as sessions_count')
        )->join('lessons', 'lessons.id', '=', 'student_lessons.id_lesson')
        ->join('lesson_objetive_sessions', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
        ->join('sessions', 'sessions.id', '=', 'lesson_objetive_sessions.id_session')
        ->join('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
        ->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
        ->where('student_lessons.id_student', $id_student)
        ->groupBy('stages.name')
        ->pluck('sessions_count', 'stage_name');

        $sessions = studentLesson::select(
            "stages.id as id_stage",
            "students.user_identification as student_identification",
            "students.id as id_student",
            "students.name as student_name",
            "stages.name as stage_name",
            'sessions.id as id_session',
            "sessions.name as session_name",
            "sessions.session_objetive",
            "sessions.approvation_standard",
            "lessons.id as lesson_id",
            "lessons.name as lesson_name",
            "lessons.file as lesson_file",
            "student_lessons.lesson_passed as lesson_passed",
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
        ->where('student_lessons.id_student', $id_student)
        ->groupBy(
            'students.user_identification', 'students.id', 'students.name',
            'stages.name', 'sessions.name', 'sessions.session_objetive',
            'sessions.approvation_standard', 'lessons.name', 'sessions.id',
            'student_lessons.lesson_passed', 'flight_objetives.name',
            'flight_history.type_flight', 'stages.id','lessons.file', 'lessons.id', 'stages.id'
        );

        $sessions = $sessions->get()->sortBy(function ($session) {
            preg_match('/\d+/', $session->session_name, $matches);
            return (int) $matches[0];
        });

        // Obtener el conteo de sesiones por etapa
        $sessionsCount = $sessionsCountQuery->toArray();

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
            $lessonFile = $session->lesson_file;
            $lessonPassed = $session->lesson_passed;
            $flightType = $session->type_flight;
            $stageSessionsCount = $sessionsCount[$stageName] ?? 0;
            $id_stage = $session->id_stage;
            $id_session = $session->id_session;


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
                    'id_stage' => $id_stage,
                    'stage_name' => $stageName,
                    'sessions_count' => $stageSessionsCount,
                    'sessions' => [],
                ];
            }

            if (!isset($result[$studentName]['stages'][$stageName]['sessions'][$sessionName])) {
                $result[$studentName]['stages'][$stageName]['sessions'][$sessionName] = [
                    'session_objetive' => $sessionObjetive,
                    'approvation_standard' => $sessionApprovationStandard,
                    'id_session' => $id_session,
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

            $lessonKey = $lessonName . '-' . $lessonFile;
            if (!array_key_exists($lessonKey, $result[$studentName]['stages'][$stageName]['sessions'][$sessionName]['flight_objetive'][$flightObjetiveName]['lessons'])) {
                $result[$studentName]['stages'][$stageName]['sessions'][$sessionName]['flight_objetive'][$flightObjetiveName]['lessons'][$lessonKey] = [
                    'lesson_id' => $session->lesson_id,
                    'lesson_name' => $lessonName,
                    'lesson_passed' => $lessonPassed,
                    'lesson_file' => $lessonFile
                ];
            }
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
                        $lessons = array_values($flightObjective['lessons']);
                        $flightObjective['lessons'] = $lessons;
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


    public function showLessons($id_student){

        $sessions = StageSession::select(
            "students.name as student_name",
            "sessions.id as session_id",
            "stages.name as stage_name",
            "sessions.name as session_name",
            "student_lessons.lesson_passed as session_passed"
        )->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
        ->join('sessions', 'sessions.id', '=', 'stage_sessions.id_session')
        ->join('lesson_objetive_sessions', 'lesson_objetive_sessions.id_session', '=', 'sessions.id')
        ->join('lessons', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
        ->join('student_lessons', 'student_lessons.id_lesson', '=', 'lessons.id')
        ->join('students', 'students.id', '=', 'student_lessons.id_student')
        ->where('student_lessons.id_student', $id_student)
        ->groupBy('stages.name', 'sessions.name', 'student_lessons.lesson_passed', 'sessions.id', 'students.name');




        $sessions = $sessions->get()->sortBy(function ($session) {
            preg_match('/\d+/', $session->session_name, $matches);
            return (int) $matches[0];
        });

        $stages = [];

        foreach ($sessions as $session) {
            $studentName = $session->student_name;
            $sessionId = $session->session_id;
            $stageName = $session->stage_name;
            $sessionName = $session->session_name;
            $sessionPassed = $session->session_passed;

            if (!isset($stages[$stageName])) {
                $stages[$stageName] = [
                    'student_name' => $studentName,
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
    public function create(Request $request)
    {

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
    public function IndexEditSyllabus(int $id_flight, int $id_student)
    {
        $sessionQuery = flightHistory::select('students.name as student_name',
                'stages.name as stage_name',
                'sessions.name as session_name',
                'flight_objetives.name as flight_objetive_name',
                'lessons.id as lesson_id',
                'lessons.name as lesson_name',
                'sessions.session_objetive as session_objetive',
                'sessions.approvation_standard as approvation_standard',
                'student_lessons.lesson_passed as lesson_passed',
            )
            ->join('sessions', 'sessions.id', '=', 'flight_history.id_session')
            ->join('stage_sessions', 'stage_sessions.id_session', '=', 'sessions.id')
            ->join('stages', 'stages.id', '=', 'stage_sessions.id_stage')
            ->join('lesson_objetive_sessions', 'lesson_objetive_sessions.id_session', '=', 'sessions.id')
            ->join('flight_objetives', 'flight_objetives.id', '=', 'lesson_objetive_sessions.id_flight_objetive')
            ->join('lessons', 'lessons.id', '=', 'lesson_objetive_sessions.id_lesson')
            ->join('student_lessons', 'lessons.id', '=', 'student_lessons.id_lesson')
            ->join('students', 'students.id', '=', 'student_lessons.id_student')
            ->where('flight_history.id', '=', $id_flight)
            ->where('students.id', '=', $id_student)
        ->get();
        $result = [];

        foreach ($sessionQuery as $session){
            $studentName = $session->student_name;
            $stageName = $session->stage_name;
            $sessionName = $session->session_name;
            $flightObjetiveName = $session->flight_objetive_name;
            $lessonName = $session->lesson_name;
            $sessionObjetive = $session->session_objetive;
            $approvationStandard = $session->approvation_standard;
            $lessonPassed = $session->lesson_passed;
            $lessonId = $session->lesson_id;

            if (!isset($result[$studentName])) {
                $result[$studentName] = [
                    'student_name' => $studentName,
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
                    'session_name' => $sessionName,
                    'session_objetive' => $sessionObjetive,
                    'approvation_standard' => $approvationStandard,
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
                'lesson_id' => $lessonId,
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
