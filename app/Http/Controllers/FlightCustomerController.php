<?php

namespace App\Http\Controllers;

use App\Models\FlightCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FlightCustomerController extends Controller
{

    private $userController;
    private $paymentMethodIdController;

    public function __construct(UserController $userController, PaymentMethodController $paymentMethodIdController)
    {
        $this->userController = $userController;
        $this->paymentMethodIdController = $paymentMethodIdController;
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
     *{
            "customer_name": "Marco Chavez Baltierrez",
            "customer_email": "marco1102004@gmail.com",
            "customer_phone": "6242647089",
            "id_flight_type": "1",
            "flight_hours": 4,
            "flight_weight": 9,
            "flight_passengers": "2",
            "flight_reservation_date": "2024-07-18",
            "flight_reservation_hour": "13:13",
            "payment_method": "2",
            "total_price": 3200
        }
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeReservationFlight(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|string|email|max:255',
            'customer_phone' => 'required|string|max:10',
            'id_flight_type' => 'required|integer|exists:info_flights,id',
            'flight_hours' => 'required|integer',
            'flight_weight' => 'required|integer',
            'flight_passengers' => 'required|integer',
            'flight_reservation_date' => 'required|date',
            'flight_reservation_hour' => 'required',
            'payment_method' => 'required|integer|exists:payment_methods,id',
            'total_price' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $employee_identification = Auth::user()->user_identification;
        $id_employee = $this->userController->getIdEmploye($employee_identification);

        $payment_method = intval($data['payment_method']);
        $efectivoId = intval($this->paymentMethodIdController->getEfectivoId());
        $inbursaDebitoId = intval($this->paymentMethodIdController->getInbursaDebitoId());
        $inbursaCreditoId = intval($this->paymentMethodIdController->getInbursaCreditoId());

        if ($payment_method == $efectivoId
            || $payment_method == $inbursaDebitoId
            || $payment_method == $inbursaCreditoId) {
            $payment_status = 'pagado';
        } else {
            $payment_status = 'pendiente';
        }

        $flightCustomer = FlightCustomer::create([
            'name' => $data['customer_name'],
            'email' => $data['customer_email'],
            'phone' => $data['customer_phone'],
            'flight_hours' => $data['flight_hours'],
            'reservation_date' => $data['flight_reservation_date'],
            'reservation_hour' => $data['flight_reservation_hour'],
            'weight' => $data['flight_weight'],
            'number_of_passengers' => $data['flight_passengers'],
            'payment_status' => $payment_status,
            'flight_status' => 'Pendiente',
            'total' => $data['total_price'],
            'id_employee' => $id_employee,
            'id_flight' => $data['id_flight_type'],
            'id_payment_method' => $data['payment_method']
        ]);

        return response()->json($flightCustomer, 201);
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




