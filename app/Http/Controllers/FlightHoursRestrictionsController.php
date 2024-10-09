<?php

namespace App\Http\Controllers;

use App\Models\FlightHoursRestrictions;
use App\Models\Option;
use App\Models\RestrictionDay;
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
    // Recuperar RestrictionDay con sus relaciones
    $restrictions = RestrictionDay::with(['FlightRestriction', 'Day'])->get();

    $canReservate = Option::select('option_type', 'is_active')
        ->where('option_type', 'can_reservate_flight')
        ->first();
    // Formato final para FullCalendar
    $calendarEvents = [];

    // Recorrer las restricciones agrupadas por id_flight_restriction
    foreach ($restrictions as $restriction) {
        $flightRestriction = $restriction->flightRestriction;
        $dayValue = $restriction->day->value; // Ej: "Lun", "Mar", etc.

        // Generar el evento base
        $event = [
            'id' => $restriction->id_flight_restriction,
            'flight_status' => 'restriction',
            'title' => $flightRestriction->motive,
            'start' => $flightRestriction->start_date . 'T' . $flightRestriction->start_hour,
            'end' => $flightRestriction->start_date . 'T' . $flightRestriction->end_hour,
            'source' => 'restriction',
            'can_reservate' => $canReservate->is_active
        ];

        // Asegúrate de que la fecha de fin sea posterior a la de inicio
        if ($this->isValidEvent($event['start'], $event['end'])) {
            // Si el día es un día de la semana (Ej: Lun, Mar, etc.), repetir en esas fechas
            if ($this->isWeekday($dayValue)) {
                // Generar las fechas recurrentes para este día
                $recurrentEvents = $this->generateRecurrentEvents($event, $dayValue);
                $calendarEvents = array_merge($calendarEvents, $recurrentEvents);
            } else {
                // Si es una restricción en una fecha específica, agregarla tal cual
                $calendarEvents[] = $event;
            }
        }
    }

    // Devolver la respuesta en formato JSON
    return response()->json($calendarEvents);
}

/**
 * Verifica si la fecha de inicio es anterior a la de finalización
 */
private function isValidEvent($start, $end)
{
    return \Carbon\Carbon::parse($start)->lt(\Carbon\Carbon::parse($end));
}

/**
 * Verifica si un valor del día es un día de la semana (Lun, Mar, Mié, etc.)
 */
private function isWeekday($dayValue)
{
    $weekdays = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    return in_array($dayValue, $weekdays);
}

/**
 * Genera eventos recurrentes para un día de la semana (Lun, Mar, Mié, etc.)
 */
private function generateRecurrentEvents($event, $dayValue)
{
    $recurrentEvents = [];

    // Mapeo del día en número (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
    $dayMap = [
        'Dom' => 0, 'Lun' => 1, 'Mar' => 2, 'Mié' => 3,
        'Jue' => 4, 'Vie' => 5, 'Sáb' => 6
    ];

    // Obtener el número del día de la semana
    $dayOfWeek = $dayMap[$dayValue];

    // Obtener la fecha actual y generar eventos para las próximas semanas
    $currentDate = now();
    $endDate = now()->addMonths(1); // Generar eventos para el próximo mes (ajustable)

    // Ajustar currentDate al próximo día correspondiente si es necesario
    if ($currentDate->dayOfWeek !== $dayOfWeek) {
        // Establecer la fecha al próximo día de la semana correspondiente
        $currentDate = $currentDate->next($dayOfWeek);
    } else {
        // Si ya es el día correspondiente, comenzamos desde aquí
        $currentDate = $currentDate->copy();
    }

    // Iterar sobre las semanas y generar eventos para el día específico
    while ($currentDate->lte($endDate)) {
        // Clonar el evento y cambiarle la fecha de inicio y fin
        $recurrentEvent = $event;
        $recurrentEvent['start'] = $currentDate->format('Y-m-d') . 'T' . substr($event['start'], 11);
        $recurrentEvent['end'] = $currentDate->format('Y-m-d') . 'T' . substr($event['end'], 11);

        // Agregarlo al arreglo de eventos recurrentes
        $recurrentEvents[] = $recurrentEvent;

        // Avanzar a la próxima semana
        $currentDate->addWeek();
    }

    return $recurrentEvents;
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
