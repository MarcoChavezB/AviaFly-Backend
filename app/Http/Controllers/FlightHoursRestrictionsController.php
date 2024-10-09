<?php

namespace App\Http\Controllers;

use App\Models\FlightHoursRestrictions;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlightHoursRestrictionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $restrictions = FlightHoursRestrictions::with('flight')->get();
        return response()->json($restrictions);
    }

    public function create(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'motive' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date',
            'start_hour' => 'required|string',
            'end_hour' => 'required|string',
            'id_flight' => 'required|integer|exists:info_flights,id',
            'days' => 'required|array',
            'days.*' => 'integer|between:1,7',
        ]);

        if($validator->fails()){
            return response()->json(["error" => $validator->errors()]);
        }

        $restriction = FlightHoursRestrictions::create([
            'motive' => $data['motive'],
            'description' => $data['description'],
            'start_date' => $data['date'],  // Cambiado de 'date' a 'start_date'
            'start_hour' => $data['start_hour'],
            'end_hour' => $data['end_hour'],
            'id_flight' => $data['id_flight'],
        ]);

        foreach ($data['days'] as $day) {
            DB::table('restriction_days')->insert([
                'id_day' => $day,
                'id_flight_restriction' => $restriction->id,
            ]);
        }

        return response()->json([
            'message' => 'Restricción de vuelo creada con éxito.',
            'restriction' => $restriction,
        ], 201);
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
     * @param  \App\Models\FlightHoursRestrictions  $flightHoursRestrictions
     * @return \Illuminate\Http\Response
     */
    public function show(FlightHoursRestrictions $flightHoursRestrictions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FlightHoursRestrictions  $flightHoursRestrictions
     * @return \Illuminate\Http\Response
     */
    public function edit(FlightHoursRestrictions $flightHoursRestrictions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FlightHoursRestrictions  $flightHoursRestrictions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FlightHoursRestrictions $flightHoursRestrictions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FlightHoursRestrictions  $flightHoursRestrictions
     * @return \Illuminate\Http\Response
     */
    public function destroy(FlightHoursRestrictions $flightHoursRestrictions)
    {
        //
    }
}
