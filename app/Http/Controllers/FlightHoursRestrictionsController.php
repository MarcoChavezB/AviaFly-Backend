<?php

namespace App\Http\Controllers;

use App\Models\FlightHoursRestrictions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlightHoursRestrictionsController extends Controller
{

public function indexDetails()
{
    $restrictions = FlightHoursRestrictions::with('flight')->get();
    return response()->json($restrictions);
}

public function indexRestrictionCalendar(){
    $restrictions = FlightHoursRestrictions::with(['flight'])->get();

    $calendarRestrictions = [];

    foreach ($restrictions as $restriction) {
        $start_date = Carbon::parse($restriction->start_date);
        $end_date = $restriction->end_date ? Carbon::parse($restriction->end_date) : null;

        // Comprobar si hay una fecha de fin y si es válida
        if ($end_date) {
            // Iterar sobre el rango de fechas (incluyendo start_date y end_date)
            $currentDate = $start_date->copy();

            while ($currentDate <= $end_date) {
                $calendarRestrictions[] = [
                    'id' => $restriction->id,  // ID de la restricción
                    'flight_status' => 'restriction',  // Estado del vuelo
                    'title' => $restriction->motive,  // Motivo de la restricción
                    'start' => $currentDate->toDateString() . 'T00:00',  // Fecha de inicio con hora de inicio (por ejemplo, a las 00:00)
                    'end' => $currentDate->toDateString() . 'T23:59',  // Fecha de fin con hora de fin (por ejemplo, a las 23:59)
                    'source' => 'restriction',  // Fuente
                    'can_reservate' => null,  // No se puede reservar (null si no está definido)
                ];

                // Avanzar al siguiente día
                $currentDate->addDay();
            }
        } else {
            // Si no hay fecha de fin, solo se agrega la fecha de inicio
            $calendarRestrictions[] = [
                'id' => $restriction->id,
                'flight_status' => 'restriction',
                'title' => $restriction->motive,
                'start' => $start_date->toDateString() . 'T00:00',
                'end' => $start_date->toDateString() . 'T23:59',
                'source' => 'restriction',
                'can_reservate' => null,
            ];
        }
    }

    return response()->json($calendarRestrictions);  // Retornar el resultado en formato JSON
}




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*
     * payload:
        {
            "motive": "motivo",
            "start_date": "2024-11-08",
            "end_date": "2024-11-09",
            "start_hour": "09:00",
            "end_hour": "18:08",
            "id_flight": "2"
        }
     * }
     */

    public function create(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'motive' => 'required|string',
            'start_date' => 'required|string',
            'start_hour' => 'required|string',
            'end_hour' => 'required|string',
            'id_flight' => 'required|integer|exists:info_flights,id',
        ]);

        if($validator->fails()){
            return response()->json(["error" => $validator->errors()]);
        }

        $restriction = new FlightHoursRestrictions();
        $restriction->motive = $data['motive'];
        $restriction->start_hour = $data['start_hour'];
        $restriction->end_hour = $data['end_hour'];
        $restriction->start_date = $data['start_date'];
        $restriction->end_date = $data['end_date'] ?? null;
        $restriction->id_flight = $data['id_flight'];

        // Guarda el registro en la base de datos
        $restriction->save();

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
    public function destroy($id_restriction)
    {
        $restrictionFlight = FlightHoursRestrictions::find($id_restriction);

        if ($restrictionFlight) {
            $restrictionFlight->delete();
        }

        return response()->json(['message' => "Restriction and related flight restriction deleted successfully"], 200);
    }
}
