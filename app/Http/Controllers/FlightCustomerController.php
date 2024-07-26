<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\FlightCustomer;
use App\Models\InfoFlight;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlightCustomerController extends Controller
{

    private $userController;
    private $paymentMethodIdController;
    private $infoFlightController;
    private $airplaneController;


    public function __construct(UserController $userController, PaymentMethodController $paymentMethodIdController, InfoFlightController $infoFlightControlle, AirPlaneController $airplaneController)
    {
        $this->userController = $userController;
        $this->paymentMethodIdController = $paymentMethodIdController;
        $this->infoFlightController = $infoFlightControlle;
        $this->airplaneController = $airplaneController;
    }



    /**
     * Display a listing of the resource.
     *
     *  {
           id_reservacion
           nombre_cliente
           telefono_cliente
            fecha_reservacion
            hora_reservacion
            numero de pasajeros
            peso de pasajeros
            estatus de vuelo
            empleado que realizo la reservacion
            equipo de vuelo
            metodo de pago
            estado de pago
            total
        }
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = FlightCustomer::select(
            'flight_customers.id as id_reservacion',
            'employees.name as employee_name',
            'flight_customers.name as client_name',
            'flight_customers.phone as client_phone',
            'flight_customers.reservation_date',
            'flight_customers.reservation_hour',
            'flight_customers.number_of_passengers as passengers',
            'flight_customers.weight as total_weight',
            'flight_customers.flight_status',
            'info_flights.equipo as flight_equipment',
            'payment_methods.type as payment_method',
            'flight_customers.payment_status as payment_status',
            'flight_customers.total'
        )
        ->join('employees', 'employees.id', '=', 'flight_customers.id_employee')
        ->join('info_flights', 'info_flights.id', '=', 'flight_customers.id_flight')
        ->join('payment_methods', 'payment_methods.id', '=', 'flight_customers.id_payment_method')
        ->orderBy('flight_customers.created_at', 'desc')
        ->get();

        return response()->json($query, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     * Payload:
        {
            "id_flight_type": "1",
            "id_pilot": "1",
            "flight_hours": 1,
            "flight_passengers": "3",
            "flight_reservation_date": "2024-07-26",
            "flight_reservation_hour": "13:28",
            "payment_method": "1",
            "total_price": 800,
            "first_passenger_weight": 30,
            "first_passenger_name": "marco",
            "first_passenger_age": "20",
            "second_passenger_weight": 100,
            "second_passenger_name": "carlos",
            "second_passenger_age": 30,
            "tird_passenger_weight": 200,
            "tird_passenger_name": "pedro",
            "tird_passenger_age": 40,
            "pilot_weight": 100,
            "total_weight": 430
        }
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeReservationFlight(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id_flight_type' => 'required|exists:info_flights,id',
            'id_pilot' => 'required|exists:users,id',
            'flight_hours' => 'required|numeric',
            'flight_passengers' => 'required|numeric|min:1|max:3',
            'flight_reservation_date' => 'required',
            'flight_reservation_hour' => 'required',
            'payment_method' => 'required|exists:payment_methods,id',
            'total_price' => 'required',
            'first_passenger_weight' => 'required|numeric',
            'first_passenger_name' => 'required',
            'first_passenger_age' => 'required',
            'pilot_weight' => 'required|numeric',
            'total_weight' => 'required|numeric',
            'id_air_planes' => 'exists:air_planes,id'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $userIdentification = Auth::user()->user_identification;
        $employeeId = $this->userController->getIdEmploye($userIdentification);

        // validacion de la fecha y hora de la reservacion
        if($this->infoFlightController->OtherFlightReserved($data['flight_reservation_date'], $data['flight_reservation_hour'], $data['flight_hours'] ,$data['id_flight_type'])){
            return response()->json(['message' => 'Las fechas coinciden con otra reservacion'], 400);
        }

        // validacion del peso total del avion seleccionado
        if($data['id_airplane'] != 0 && $this->airplaneController->totalWeightExceded($data['id_airplane'], $data['total_weight'])){
            return response()->json(['message' => 'El peso total excede el limite del avion'], 400);
        }

        if(intval($data['payment_method']) === $this->paymentMethodIdController->getEfectivoId()){
            $paymentStatus = 'pagado';
        } else {
            $paymentStatus = 'pendiente';
        }


        $flightCustomer = new FlightCustomer();
        $flightCustomer->first_passenger_name = $data['first_passenger_name'];
        $flightCustomer->first_passenger_age = $data['first_passenger_age'];
        $flightCustomer->first_passenger_weight = $data['first_passenger_weight'];

        if($data['flight_passengers'] > 1){
            $flightCustomer->second_passenger_name = $data['second_passenger_name'];
            $flightCustomer->second_passenger_age = $data['second_passenger_age'];
            $flightCustomer->second_passenger_weight = $data['second_passenger_weight'];
        }

        if($data['flight_passengers'] > 2){
            $flightCustomer->tird_passenger_name = $data['tird_passenger_name'];
            $flightCustomer->tird_passenger_age = $data['tird_passenger_age'];
            $flightCustomer->tird_passenger_weight = $data['tird_passenger_weight'];
        }

        $flightCustomer->pilot_weight = $data['pilot_weight'];
        $flightCustomer->flight_hours = $data['flight_hours'];
        $flightCustomer->reservation_date = $data['flight_reservation_date'];
        $flightCustomer->reservation_hour = $data['flight_reservation_hour'];
        $flightCustomer->total_weight = $data['total_weight'];
        $flightCustomer->number_of_passengers = $data['flight_passengers'];

        $flightCustomer->payment_status = $paymentStatus;
        $flightCustomer->flight_status = 'pendiente';
        $flightCustomer->total = $data['total_price'];

        $flightCustomer->id_employee = $employeeId;
        $flightCustomer->id_flight = $data['id_flight_type'];
        $flightCustomer->id_air_planes = $data['id_airplane'];
        $flightCustomer->id_payment_method = $data['payment_method'];
        $flightCustomer->id_pilot = $data['id_pilot'];

        $flightCustomer->save();

        return response()->json(['message' => 'Reservacion creada con exito'], 201);
    }

    public function create()
    {
        //
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







