<?php

namespace App\Http\Controllers;

use App\Models\NewSletter;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Models\NewSletterStudentsEmployee;
use Illuminate\Support\Facades\Auth;

class NewSletterController extends Controller
{
    protected $userController;
    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $user_type = $user->user_type;

        $newSlettersQuery = NewSletter::select(
                'new_sletter_students_employees.id as id_new_sletter',
                'new_sletters.title',
                'new_sletters.content',
                'employees.name as created_by',
                'new_sletters.direct_to',
                'new_sletters.file',
                'new_sletter_students_employees.is_read',
                'new_sletters.start_at',
                'new_sletters.expired_at',
                'new_sletters.created_at'
            )
            ->join('new_sletter_students_employees', 'new_sletter_students_employees.id_new_sletter', '=', 'new_sletters.id')
            ->leftJoin('students', 'students.id', '=', 'new_sletter_students_employees.id_student')
            ->leftJoin('employees', 'employees.id', '=', 'new_sletter_students_employees.id_employee');

        if ($user_type !== 'root') {
            switch ($user_type) {
                case 'student':
                    $newSlettersQuery->where('new_sletters.direct_to', 'estudiantes');
                    break;
                case 'employee':
                    $newSlettersQuery->where('new_sletters.direct_to', 'empleados');
                    break;
                case 'instructor':
                    $newSlettersQuery->where('new_sletters.direct_to', 'instructores');
                    break;
            }
        }

        $newSletters = $newSlettersQuery->get();
        return response()->json($newSletters);
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NewSletter  $newSletter
     * @return \Illuminate\Http\Response
     */
    public function show(NewSletter $newSletter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NewSletter  $newSletter
     * @return \Illuminate\Http\Response
     */
    public function edit(NewSletter $newSletter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NewSletter  $newSletter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewSletter $newSletter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewSletter  $newSletter
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewSletter $newSletter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NewSletter  $newSletter
     * @return \Illuminate\Http\Response
     * @param $id_new_sletter: number
     */
    public function markAsRead($id_new_sletter)
    {
        $newSletter = NewSletterStudentsEmployee::find($id_new_sletter);
        $newSletter->is_read = 1;
        $newSletter->save();

        return response()->json(['message' => 'Se ha marcado como leído correctamente.']);
    }
}
