<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FlightCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FlightCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
public function storeReservationFlight(Request $request)
{
    $data = $request->all();
    $validator = Validator::make($data, [
        'name' => 'required',
        'email' => 'required|email|unique:students',
        'phone' => 'required|unique:students',
        'flight_type' => 'required|in:simulador',
        'flight_hours' => 'required|integer',
        'reservation_date' => 'required|date',
        'reservation_hour' => 'required',
        'payment_method' => 'required|in:tarjeta,efectivo',
        'total' => 'required|numeric',
    ],[
        'name.required' => 'El campo nombre es requerido',
        'email.required' => 'El campo email es requerido',
        'email.email' => 'El campo email debe ser un email válido',
        'phone.required' => 'El campo teléfono es requerido',
        'phone.unique' => 'El teléfono ya ha sido registrado',
        'flight_type.required' => 'El campo tipo de vuelo es requerido',
        'flight_type.in' => 'El campo tipo de vuelo debe ser simulador',
        'flight_hours.required' => 'El campo horas de vuelo es requerido',
        'flight_hours.integer' => 'El campo horas de vuelo debe ser un número entero',
        'reservation_date.required' => 'El campo fecha de reservación es requerido',
        'reservation_date.date' => 'El campo fecha de reservación debe ser una fecha válida',
        'reservation_hour.required' => 'El campo hora de reservación es requerido',
        'total.required' => 'El campo total es requerido',
        'total.numeric' => 'El campo total debe ser un número',
    ]);

    if($validator->fails()){
        return response()->json(['error' => $validator->errors()], 401);
    }
    $employee = Employee::where('user_identification', Auth::user()->user_identification)->first();
    $data['id_employee'] = $employee->id;

    DB::statement('CALL recreative_flight(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
        $data['id_employee'],
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['flight_type'],
        $data['flight_hours'],
        $data['reservation_date'],
        $data['reservation_hour'],
        $data['payment_method'],
        $data['total']
    ]);

    return response()->json(['message' => 'Reservación de vuelo recreativo realizada con éxito'], 201);
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FlightCustomer  $flightCustomer
     * @return \Illuminate\Http\Response
     */
    public function show(FlightCustomer $flightCustomer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FlightCustomer  $flightCustomer
     * @return \Illuminate\Http\Response
     */
    public function edit(FlightCustomer $flightCustomer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FlightCustomer  $flightCustomer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FlightCustomer $flightCustomer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FlightCustomer  $flightCustomer
     * @return \Illuminate\Http\Response
     */
    public function destroy(FlightCustomer $flightCustomer)
    {
        //
    }
}
