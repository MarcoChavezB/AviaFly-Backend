<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\CustomerPayment;
use App\Models\Employee;
use App\Models\FlightCustomer;
use App\Models\InfoFlight;
use App\Models\PaymentMethod;
use App\Models\RecreativeConcept;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     *      "id_reservation": number,
     *      "flight_type": number,
     *      "pilot": number,
     *      "flight_hours": number,
     *      "flight_passengers": number,
     *      "flight_reservation_date": "2024-07-26",
     *      "flight_reservation_hour": "13:28",
     *      "payment_method": number,
     *      "total_price": number,
     *      "passengers": [
     *          {
     *              "name": "marco",
     *              "age": "20",
     *              "weight": 30
     *          },
     *          {
     *          "name": "carlos",
     *          "age": "30",
     *          "weight": 100
     *          },
     *      ]
     *
     *
     *  }
     * @return \Illuminate\Http\Response
     */

public function index()
{
    $flightCustomers = DB::table('flight_customers')
        ->leftJoin('recreative_concepts', 'flight_customers.id_concept', '=', 'recreative_concepts.id')
        ->leftJoin('air_planes', 'flight_customers.id_air_planes', '=', 'air_planes.id')
        ->leftJoin('employees as employee', 'flight_customers.id_employee', '=', 'employee.id')
        ->leftJoin('employees as pilot', 'flight_customers.id_pilot', '=', 'pilot.id')
        ->leftJoin('payment_methods', 'flight_customers.id_payment_method', '=', 'payment_methods.id')
        ->leftJoin('customer_payments', 'customer_payments.id_customer_flight', '=', 'flight_customers.id')
        ->select(
            'flight_customers.*',
            'recreative_concepts.id as id_flight_type',
            'recreative_concepts.concept as flight_type',
            'air_planes.id as id_airplane',
            'employee.name as employee_name',
            'pilot.id as id_pilot',
            'pilot.name as pilot_name',
            'payment_methods.id as id_payment_method',
            'payment_methods.type as payment_method'
        )
        ->orderBy('flight_customers.created_at', 'desc')
        ->get();

    $flightCustomersArray = $flightCustomers->map(function ($flightCustomer) {
        $passengers = [];

        // Generar pasajeros dinámicamente según el número de pasajeros
        for ($i = 2; $i <= $flightCustomer->number_of_passengers; $i++) {
            $nameField = "passenger_{$i}_name";
            $ageField = "passenger_{$i}_age";
            $weightField = "passenger_{$i}_weight";

            if (!empty($flightCustomer->$nameField)) {
                $passengers[] = [
                    'name' => $flightCustomer->$nameField,
                    'age' => $flightCustomer->$ageField,
                    'weight' => $flightCustomer->$weightField,
                ];
            }
        }

        return [
            'id_reservation' => $flightCustomer->id,
            'id_flight_type' => $flightCustomer->id_flight_type ?? null,
            'id_airplane' => $flightCustomer->id_airplane ?? 0,
            'flight_type' => $flightCustomer->flight_type ?? 'N/A',
            'reservation_status' => $flightCustomer->flight_status,
            'employee' => $flightCustomer->employee_name ?? 'N/A',
            'id_pilot' => $flightCustomer->id_pilot ?? null,
            'pilot' => $flightCustomer->pilot_name ?? 'N/A',
            'flight_hours' => $flightCustomer->flight_hours,
            'flight_passengers' => $flightCustomer->number_of_passengers,
            'id_payment_method' => $flightCustomer->id_payment_method ?? null,
            'payment_method' => $flightCustomer->payment_method ?? 'N/A',
            'total_price' => $flightCustomer->total,
            'flight_reservation_date' => $flightCustomer->reservation_date,
            'flight_reservation_hour' => $flightCustomer->reservation_hour,
            'first_passenger_name' => $flightCustomer->first_passenger_name,
            'first_passenger_age' => $flightCustomer->first_passenger_age,
            'first_passenger_weight' => $flightCustomer->first_passenger_weight,
            'pilot_weight' => $flightCustomer->pilot_weight,
            'total_weight' => $flightCustomer->total_weight,
            'ticket_path' => $flightCustomer->payment_ticket ?? 'N/A',
            'passengers' => $passengers,
        ];
    });

    return response()->json($flightCustomersArray, 200);
}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *{
        "id_flight": 1,
        "initial_horometer": 1,
        "final_horometer": 3,
        "total_horometer": 565,
        "final_tacometer": 5656,
        "comments": "Simona"
     }
     */
    public function storeReportCustomer(Request $request) {
        $validator = Validator::make($request->all(), [
            'id_flight' => 'required|exists:flight_customers,id',
            'initial_horometer' => 'required|numeric',
            'final_horometer' => 'required|numeric',
            'total_horometer' => 'required|numeric',
            'final_tacometer' => 'required|numeric',
        ], [
            'initial_horometer.required' => 'El horometro inicial es requerido',
            'final_horometer.required' => 'El horometro final es requerido',
            'total_horometer.required' => 'El total de horometros es requerido',
            'final_tacometer.required' => 'El tacometro final es requerido',
            'id_flight.required' => 'El id de la reservacion es requerido',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $flightCustomer = FlightCustomer::find($request->id_flight);

        $flightCustomer->flight_status = 'realizado';
        $flightCustomer->has_report = 1;
        $flightCustomer->initial_horometer = $request->initial_horometer;
        $flightCustomer->final_horometer = $request->final_horometer;
        $flightCustomer->total_horometer = $request->total_horometer;
        $flightCustomer->final_tacometer = $request->final_tacometer;

        if ($request->has('comments')) {
            $flightCustomer->comment = $request->comments;
        }

        $flightCustomer->save();

        return response()->json(['message' => 'Reporte de vuelo creado con exito'], 201);
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
            {
                "id_flight_type": 1, // concepto recreativo
                "id_pilot": "7",
                "id_airplane": 0,
                "flight_hours": 1,
                "flight_passengers": 1,
                "flight_reservation_date": "2024-10-18",
                "flight_reservation_hour": "17:22",
                "payment_method": "7",
                "total_price": 150,
                "first_passenger_weight": 70,
                "first_passenger_name": "joijij",
                "first_passenger_age": 0,
                "second_passenger_weight": 0,
                "second_passenger_name": "",
                "second_passenger_age": 0,
                "tird_passenger_weight": 0,
                "tird_passenger_name": "",
                "tird_passenger_age": 0,
                "pilot_weight": "100.00",
                "total_weight": 170
            }
        }
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeReservationFlight(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id_flight_type' => 'required|exists:recreative_concepts,id',
            'id_pilot' => 'required|exists:employees,id',
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
        $flightCustomer->id_concept = $data['id_flight_type'];
        $flightCustomer->id_payment_method = $data['payment_method'];
        $flightCustomer->id_pilot = $data['id_pilot'];


        if($data['id_airplane'] != 0){
            $flightCustomer->id_air_planes = $data['id_airplane'];
        }

        $flightCustomer->save();

        $concept = RecreativeConcept::find($data['id_flight_type']);
        $employee = Employee::find($employeeId);
        $payment_method = PaymentMethod::find($data['payment_method']);

        $pdfController = new PDFController();
        $urlTicket = $pdfController->generateRecreativeTicket($employee, $data, $concept, $payment_method);

        $customerPayment = new CustomerPayment();
        if($data['payment_method'] != $this->paymentMethodIdController->getAbonosId()){
            $customerPayment->amount = $data['total_price'];
            $customerPayment->id_payment_method = $data['payment_method'];
            $customerPayment->id_customer_flight = $flightCustomer->id;
            $customerPayment->payment_ticket = $urlTicket;

            $customerPayment->save();
        }

        return response()->json(['message' => 'Reservacion creada con exito', 'tiket' => $urlTicket], 201);
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
    public function edit(Request $request, int $reservation_id, string $flight_status = null)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'id_flight_type' => 'nullable|exists:info_flights,id',
            'id_pilot' => 'nullable|exists:employees,id',
            'flight_hours' => 'nullable|numeric',
            'flight_passengers' => 'nullable|numeric|min:1|max:3',
            'payment_method' => 'nullable|exists:payment_methods,id',
            'first_passenger_weight' => 'nullable|numeric',
            'pilot_weight' => 'nullable|numeric',
            'total_weight' => 'nullable|numeric',
            'id_air_planes' => 'nullable|exists:air_planes,id'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        $userIdentification = Auth::user()->user_identification;
        $employeeId = $this->userController->getIdEmploye($userIdentification);

        // Validación de la fecha y hora de la reservación, excluyendo la reserva actual
        if(isset($data['flight_reservation_date']) && isset($data['flight_reservation_hour']) && isset($data['flight_hours']) && isset($data['id_flight_type']) && $this->infoFlightController->OtherFlightReserved($data['flight_reservation_date'], $data['flight_reservation_hour'], $data['flight_hours'], $data['id_flight_type'], $reservation_id)){
            return response()->json(['message' => 'Las fechas coinciden con otra reservacion'], 400);
        }

         // Validación del peso total del avión seleccionado
        if(isset($data['id_airplane']) && $data['id_airplane'] != 0 && isset($data['total_weight']) && $this->airplaneController->totalWeightExceded($data['id_airplane'], $data['total_weight'])){
            return response()->json(['message' => 'El peso total excede el limite del avion'], 400);
        }

        $paymentStatus = 'pendiente';
        if(isset($data['payment_method']) && intval($data['payment_method']) === $this->paymentMethodIdController->getEfectivoId()){
            $paymentStatus = 'pagado';
        }

        $flightCustomer = FlightCustomer::find($reservation_id);
        $flightCustomer->first_passenger_name = $data['first_passenger_name'] ?? $flightCustomer->first_passenger_name;
        $flightCustomer->first_passenger_age = $data['first_passenger_age'] ?? $flightCustomer->first_passenger_age;
        $flightCustomer->first_passenger_weight = $data['first_passenger_weight'] ?? $flightCustomer->first_passenger_weight;

        if(isset($data['flight_passengers']) && $data['flight_passengers'] > 1){
            $flightCustomer->second_passenger_name = $data['second_passenger_name'] ?? $flightCustomer->second_passenger_name;
            $flightCustomer->second_passenger_age = $data['second_passenger_age'] ?? $flightCustomer->second_passenger_age;
            $flightCustomer->second_passenger_weight = $data['second_passenger_weight'] ?? $flightCustomer->second_passenger_weight;
        }

        if(isset($data['flight_passengers']) && $data['flight_passengers'] > 2){
            $flightCustomer->tird_passenger_name = $data['tird_passenger_name'] ?? $flightCustomer->tird_passenger_name;
            $flightCustomer->tird_passenger_age = $data['tird_passenger_age'] ?? $flightCustomer->tird_passenger_age;
            $flightCustomer->tird_passenger_weight = $data['tird_passenger_weight'] ?? $flightCustomer->tird_passenger_weight;
        }

        $flightCustomer->pilot_weight = $data['pilot_weight'] ?? $flightCustomer->pilot_weight;
        $flightCustomer->flight_hours = $data['flight_hours'] ?? $flightCustomer->flight_hours;
        $flightCustomer->reservation_date = $data['flight_reservation_date'] ?? $flightCustomer->reservation_date;
        $flightCustomer->reservation_hour = $data['flight_reservation_hour'] ?? $flightCustomer->reservation_hour;
        $flightCustomer->total_weight = $data['total_weight'] ?? $flightCustomer->total_weight;
        $flightCustomer->number_of_passengers = $data['flight_passengers'] ?? $flightCustomer->number_of_passengers;

        $flightCustomer->payment_status = $paymentStatus;

        if($flight_status != null){
            $flightCustomer->flight_status = $flight_status;
        } else {
            $flightCustomer->flight_status = 'pendiente';
        }

        $flightCustomer->total = $data['total_price'] ?? $flightCustomer->total;

        $flightCustomer->id_employee = $employeeId;
        $flightCustomer->id_payment_method = $data['payment_method'] ?? $flightCustomer->id_payment_method;
        $flightCustomer->id_pilot = $data['id_pilot'] ?? $flightCustomer->id_pilot;

        if(isset($data['id_airplane']) && $data['id_airplane'] != 0){
            $flightCustomer->id_air_planes = $data['id_airplane'] ?? $flightCustomer->id_air_planes;
        }

        $flightCustomer->save();

        return response()->json(['message' => 'Reservacion actualizada con exito'], 200);
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







