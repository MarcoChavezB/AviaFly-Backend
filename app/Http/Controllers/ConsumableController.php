<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\ConsumableFlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConsumableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $results = ConsumableFlight::select(
            'consumable_flights.id as id_filter',
            'consumable_flights.created_at as fecha_sistema',
            'consumable_flights.date as fecha',
            'air_planes.model as equipo',
            'employees.name as autor',
            'consumables.name as consumible',
            'consumable_flights.liters as cantidad'
        )
        ->join('consumables', 'consumables.id', '=', 'consumable_flights.id_consumable')
        ->join('flight_history', 'flight_history.id', '=', 'consumable_flights.id_flight')
        ->join('employees', 'employees.id', '=', 'consumable_flights.id_employee')
        ->join('air_planes', 'air_planes.id', '=', 'flight_history.id_airplane')
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
     *      "liters": 100,
     *      "comments": "Comentario de prueba",
     * }
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $employeId = Auth::user()->id;

        $validator = Validator::make($data, [
            'id_consumable' => 'required|integer',
            'id_flight' => 'required|integer',
            'date' => 'required|date',
            'liters' => 'required|numeric',
            'comments' => 'string'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        ConsumableFlight::create([
            'id_consumable' => $data['id_consumable'],
            'id_flight' => $data['id_flight'],
            'date' => $data['date'],
            'liters' => $data['liters'],
            'comments' => $data['comments'],
            'id_employee' => $employeId
        ]);
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
     */
    public function update(Request $request, Consumable $consumable)
    {
        //
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
