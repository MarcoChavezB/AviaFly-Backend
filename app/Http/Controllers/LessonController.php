<?php

namespace App\Http\Controllers;

use App\Models\FlightLessons;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lessons = Lesson::select('id', 'lesson_title')->get();
        return response()->json($lessons, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexByFlight(int $id_flight)
    {
        $lessons = Lesson::select('lessons.id', 'lessons.lesson_title', 'flight_lessons.lesson_approved', 'flight_lessons.flight_id')
            ->join('flight_lessons', 'lesson_id', '=', 'lessons.id')
            ->join('flight_history', 'flight_lessons.flight_id', '=', 'flight_history.id')
            ->where('flight_history.id', $id_flight)
            ->get();
        return response()->json($lessons, 200);
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
     * @param  \App\Models\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function show(Lesson $lesson)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function edit(Lesson $lesson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,int $id_flight)
    {
        $lessons = $request->all();

        foreach ($lessons as $lessonData) {
            $id_lesson = $lessonData['id_lesson'];
            $lesson_approved = $lessonData['lesson_approved'];

            FlightLessons::where('flight_id', $id_flight)
                        ->where('lesson_id', $id_lesson)
                        ->update(['lesson_approved' => $lesson_approved]);
        }

        return response()->json(['message' => 'Lecciones actualizadas correctamente'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lesson  $lesson
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lesson $lesson)
    {
        //
    }
}