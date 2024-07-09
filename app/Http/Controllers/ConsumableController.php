<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableFlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UserController;
class ConsumableController extends Controller
{
    protected $UserController;

    public function __construct(UserController $UserController)
    {
        $this->UserController = $UserController;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = ConsumableFlight::select(
            'consumable_flights.id as id_consumable',
            'consumable_flights.created_at as fecha_sistema',
            'consumable_flights.date as fecha',
            'consumable_flights.hour as hour',
            'consumable_flights.comments as comment',
            'air_planes.model as equipo',
            'employees.name as autor',
            'consumables.name as consumible',
            'consumable_flights.liters as cantidad'
        )
        ->join('consumables', 'consumables.id', '=', 'consumable_flights.id_consumable')
        ->join('air_planes', 'air_planes.id', '=', 'consumable_flights.id_plane')
        ->join('employees', 'employees.id', '=', 'consumable_flights.id_employee')
        ->orderBy('consumable_flights.created_at', 'desc')
        ->get();
        return response()->json($results);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @RequestBody {
     *      "id_consumable": 1,
     *      "id_flight": 1,
     *      "date": "2021-10-10",
     *      "hour": "04:00:00",
     *      "liters": 100,
     *      "comments": "Comentario de prueba",
     * }
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $employeId = $this->UserController->getClientId(Auth::user()->id);

        $validator = Validator::make($data, [
            'id_consumable' => 'required|integer',
            'id_flight' => 'required|integer',
            'date' => 'required|date',
            'hour' => 'required',
            'liters' => 'required|numeric',
            'comments' => 'string'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $consumable = new ConsumableFlight();
        $consumable->id_consumable = $data['id_consumable'];
        $consumable->id_plane = $data['id_flight'];
        $consumable->date = $data['date'];
        $consumable->hour = $data['hour'];
        $consumable->liters = $data['liters'];
        $consumable->comments = $data['comments'];
        $consumable->id_employee = $employeId;
        $consumable->save();


        return response()->json(['message' => 'Consumable created'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Consumable  $consumable
     * @return \Illuminate\Http\Response
     * @Return{
     *      "id": number,
     *      "name": "string",
     * }
     */
    public function show()
    {
        $consumables = Consumable::select('id', 'name')->get();
        return response()->json($consumables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Consumable  $consumable
     * @return \Illuminate\Http\Response
     */
    public function edit(Consumable $consumable)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Consumable  $consumable
     * @return \Illuminate\Http\Response
     * @RequestBody {
     *     "id_consumable": number,
     *     "fecha_sistema": string,
     *     "fecha": string,
     *     "hour": string,
     *     "comment": string,
     *     "equipo": string,
     *     "autor": string,
     *     "consumible": string,
     *     "cantidad": number
     *  }
     *
     */
    public function update(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'id_consumable' => 'integer',
            'comment' => 'string|max:255|min:5|nullable',
            'equipo' => 'string',
            'autor' => 'string',
            'consumible' => 'string',
            'cantidad' => 'numeric'
        ]);


        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $consumable = ConsumableFlight::find($data['id_consumable']);
        $consumable->date = $data['fecha'] ?? $consumable->date;
        $consumable->hour = $data['hour'] ?? $consumable->hour;
        $consumable->comments = $data['comment'] ?? $consumable->comments;
        $consumable->liters = $data['cantidad'] ?? $consumable->liters;
        $consumable->save();

        return response()->json(['message' => 'Consumable updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Consumable  $consumable
     * @return \Illuminate\Http\Response
     */
    public function destroy(Consumable $consumable)
    {
        //
    }
}
