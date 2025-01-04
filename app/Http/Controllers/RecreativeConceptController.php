<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\CustomerPayment;
use App\Models\FlightCustomer;
use App\Models\FlightPayment;
use App\Models\PaymentMethod;
use App\Models\RecreativeConcept;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecreativeConceptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $concepts = RecreativeConcept::all();
        $airplanes = AirPlane::all();
        return response()->json(["concepts" => $concepts, "airplanes" => $airplanes]);
    }


    function reportFlight(int $id_flight_customer)
    {
        $data = FlightCustomer::select(
                'flight_customers.id as flight_customer_id',
                'customer_payments.id as payment_id',
                'customer_payments.amount',
                'customer_payments.payment_voucher',
                'customer_payments.payment_ticket',
                'customer_payments.created_at',
                'payment_methods.type',
                'flight_customers.total'
            )
            ->leftJoin('customer_payments', 'flight_customers.id', '=', 'customer_payments.id_customer_flight')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'customer_payments.id_payment_method')
            ->where('flight_customers.id', $id_flight_customer)
            ->get()
            ->groupBy('flight_customer_id') // Agrupa los pagos por vuelo
            ->map(function ($flights) {
                $flight = $flights->first();


                $history_amounts = $flights->map(function ($payment) {
                    $formatTime = Carbon::parse($payment->created_at)->format('d/m/Y H:i:s');
                    return [
                        'amount' => $payment->amount,
                        'payment_voucher' => $payment->payment_voucher,
                        'payment_ticket' => $payment->payment_ticket,
                        'created_at' => $formatTime,
                        'payment_method' => $payment->type,
                    ];
                });

                return [
                    'id_customer_flight' => $flight->flight_customer_id,
                    'total' => $flight->total,
                    'history_amounts' => $history_amounts
                ];
            })
            ->values();
        return response()->json($data->first(), 200);
    }
    /*
     * payload: {
        {
            "id_flight_history": 8,
            "installment_value": 200.75,
            "payment_method": 1
        }
     * }
     */
    public function storeInstallment(Request $request){
        $paymentController = new PaymentMethodController();
        $recreativeFlight = FlightCustomer::find($request->id_flight_history);

        if($request->installment_value == 0){
            return response()->json(["msg" => "El monto no puede ser 0"], 400);
        }

        if(!$recreativeFlight){
            return response()->json(["msg" => "Object not found"], 404);
        }

        if($recreativeFlight->flight_status == 'cancelado'){
            return response()->json(["msg" => "no se puede abonar un vuelo cancelado"], 400);
        }

        if($recreativeFlight->id_payment_method != $paymentController->getAbonosId()){
            return response()->json(["msg" => "el metodo de pago no admite abonos"], 400);
        }


        if($request->installment_value > $recreativeFlight->total){
            return response()->json(["msg" => "no se puede abonar mas del total"], 400);
        }

        $paymentRecreative = new CustomerPayment();

        $paymentRecreative->amount = $request->installment_value;
        $paymentRecreative->id_payment_method = $request->payment_method;
        $paymentRecreative->id_customer_flight = $request->id_flight_history;

        $paymentRecreative->save();

        return response()->json(["msg" => "Abono creado con exito"]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RecreativeConcept  $recreativeConcept
     * @return \Illuminate\Http\Response
     */
    public function show(RecreativeConcept $recreativeConcept)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RecreativeConcept  $recreativeConcept
     * @return \Illuminate\Http\Response
     */
    public function edit(RecreativeConcept $recreativeConcept)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RecreativeConcept  $recreativeConcept
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecreativeConcept $recreativeConcept)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RecreativeConcept  $recreativeConcept
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_customer_flight){

        $customer_flight = FlightCustomer::find($id_customer_flight);
        if(!$customer_flight){
            return response()->json(['resp' => 'the id provided not exist']);
        }

        $customer_flight->delete();

        return response()->json(['resp' => 'the register has been deleted succefully']);
    }
}

















