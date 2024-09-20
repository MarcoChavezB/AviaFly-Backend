<?php

namespace App\Http\Controllers;

use App\Models\NewSletter;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewSletterController extends Controller
{
    protected $userController, $fileController;
    public function __construct(UserController $userController, FileController $fileController)
    {
        $this->userController = $userController;
        $this->fileController = $fileController;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @param $request: request
     * @Documentation: este metodo primero obtiene el usuario autenticado
     *  luego obtiene el tipo de usuario
     */
    public function index()
    {
        $user = Auth::user();
        $user_type = $user->user_type;
        $base = $this->userController->getBaseAuth($user);

        // Query to get newsletters with the necessary joins
        $newSlettersQuery = NewSletter::select(
                'new_sletters.id as id_newsletter',
                'new_sletters.id_base',
                'new_sletters.is_active',
                'new_sletters.created_by as created_by_id',
                'employees.name as created_by',
                'new_sletters.title',
                'new_sletters.content',
                'new_sletters.direct_to',
                'new_sletters.file',
                'new_sletters.start_at as start_date',
                'new_sletters.expired_at as expired_date',
                'new_sletters.created_at as created_date',
            )
            ->leftJoin('employees', 'employees.id', '=', 'new_sletters.created_by')
            ->OrderBy('new_sletters.created_at', 'desc');

        // Apply filters based on user type
        if ($user_type !== 'root') {
            switch ($user_type) {
                case 'student':
                    $newSlettersQuery->where('new_sletters.direct_to', 'estudiantes')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'employee':
                    $newSlettersQuery
                        ->where('new_sletters.direct_to', 'empleados')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'instructor':
                    $newSlettersQuery->where('new_sletters.direct_to', 'instructores')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
                case 'flight_instructor':
                    $newSlettersQuery->where('new_sletters.direct_to', 'flight_instructor')
                        ->orWhere('new_sletters.direct_to', 'todos');
                    break;
            }
        }

        // Apply base filter
        $newSlettersQuery->whereExists(function ($query) use ($base) {
            $query->select(DB::raw(1))
                  ->from('employees as e')
                  ->whereRaw('e.id = new_sletters.created_by')
                  ->where('e.id_base', $base->id);
        });

        $newSletters = $newSlettersQuery->get();

       $client_id = $this->userController->getClientId($user->id);


        // is ownser of the newsletter
        $newSletters = $newSletters->map(function ($newsletter) use ($client_id) {
            $newsletter->is_owner = $newsletter->created_by_id == $client_id;
            return $newsletter;
        });

        // Transform is_active from 0/1 to false/true
        $newSletters = $newSletters->map(function ($newsletter) {
            $newsletter->is_active = $newsletter->is_active == 1;
            return $newsletter;
        });

        return response()->json($newSletters);
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @param $request: request-
     * @Payload: {
     *      "title": "string",
     *      "content": "string",
     *      "start_date": "string",
     *      "expired_date": "string",
     *      "direct_to": enum('todos', 'empleados', 'instructores', 'estudiantes),
     *      "base_id": "number",
     *      "file": "file"
     * }
     */

    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'title' => 'required|string',
            'content' => 'required|string',
            'start_date' => 'required|date',
            'expired_date' => 'required|date',
            'direct_to' => 'required|in:todos,empleados,instructores,estudiantes,flight_instructor',
            'base_id' => 'required|numeric',
        ], [
            'title.required' => 'El título es requerido.',
            'content.required' => 'El contenido es requerido.',
            'start_date.required' => 'La fecha de inicio es requerida.',
            'expired_date.required' => 'La fecha de expiración es requerida.',
            'direct_to.required' => 'El destinatario es requerido.',
            'base_id.required' => 'La base es requerida.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if ($request->hasFile('file')) {
            $url = $this->fileController->saveFile($request->file('file'), $data['base_id'], $data['direct_to'], $this->fileController->getBasePath());
        }

        $created_by = Employee::where('user_identification', Auth::user()->user_identification)->first();

        $newSletter = new Newsletter();

        $newSletter->title = $data['title'];
        $newSletter->content = $data['content'];
        $newSletter->start_at = $data['start_date'];
        $newSletter->expired_at = $data['expired_date'];
        $newSletter->direct_to = $data['direct_to'];
        $newSletter->id_base = $data['base_id'];
        $newSletter->created_by = $created_by->id;
        $newSletter->file = $url ?? null;

        $newSletter->save();

        return response()->json([
            'message' => 'Se ha creado el boletín correctamente.',
            'url' => $url ?? null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     * @param $request: request
     * @Payload: {
     *      "id_newsletter": number,
            "id_base": number,
            "is_active": string,
            "created_by_id": number,
            "created_by": string,
            "title": string,
            "content": string,
            "direct_to": enum('todos', 'empleados', 'instructores', 'estudiantes'),
            "file": null,
            "start_date": string,
            "expired_date": string,
            "created_date": string,
            "is_owner": boolean
        }
     *
     */
    public function edit(Request $request)
    {
        $data = $request->all();
        $userId = $this->userController->getIdEmploye(Auth::user()->user_identification);

        $validator = Validator::make($data, [
            'id_newsletter' => 'numeric|exists:new_sletters,id',
            'id_base' => 'numeric|exists:bases,id',
            'created_by_id' => 'numeric|exists:employees,id',
            'created_by' => 'string',
            'title' => 'string',
            'content' => 'string',
            'file' => 'nullable|file',
            'start_date' => 'date',
            'expired_date' => 'date',
            'created_date' => 'date',
        ], [
            'id_base.numeric' => 'La base debe ser un número.',
            'id_base.exists' => 'La base no existe.',
            'created_by_id.numeric' => 'El creador debe ser un número.',
            'created_by_id.exists' => 'El creador no existe.',
            'created_by.string' => 'El creador debe ser un string.',
            'title.string' => 'El título debe ser un string.',
            'content.string' => 'El contenido debe ser un string.',
            'file.nullable' => 'El archivo debe ser nulo.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha.',
            'expired_date.date' => 'La fecha de expiración debe ser una fecha.',
            'created_date.date' => 'La fecha de creación debe ser una fecha.',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $newSletter = NewSletter::find($data['id_newsletter']);

        if($newSletter->created_by != $userId){
            return response()->json(['errors' => ['No tienes permisos para editar este boletin.']], 403);
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            if ($newSletter->file) {
                $this->fileController->deleteFile($newSletter->file);
            }

            $url = $this->fileController->saveFile($request->file('file'), $data['id_base'], $data['direct_to'], $this->fileController->getBasePath());
            $newSletter->file = $url;
        }

        $newSletter->id_base = $data['id_base'] ?? $newSletter->id_base;
        $newSletter->is_active = $data['is_active'] == 'true' ? true: false ?? $newSletter->is_active;
        $newSletter->title = $data['title'] ?? $newSletter->title;
        $newSletter->content = $data['content'] ?? $newSletter->content;
        $newSletter->direct_to = $data['direct_to'] ?? $newSletter->direct_to;
        $newSletter->start_at = $data['start_date'] ?? $newSletter->start_at;
        $newSletter->expired_at = $data['expired_date'] ?? $newSletter->expired_at;
        $newSletter->created_at = $data['created_date'] ?? $newSletter->created_at;

        $newSletter->save();

        return response()->json(['message' => 'Se ha actualizado el boletin correctamente.']);
    }
}
