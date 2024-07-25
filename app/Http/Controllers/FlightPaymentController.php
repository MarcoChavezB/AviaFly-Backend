<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use App\Models\InfoFlight;
use Illuminate\Http\Request;

class FlightPaymentController extends Controller
{
    function getFlightPrice(Request $request)
    {
        $flightType = $request->input('id_flight_type');
        $flightHours = floatval($request->input('flight_hours'));

        $priceInfo = InfoFlight::where('id', $flightType)->first(['price']);
        $pricePerHour = floatval($priceInfo->price);

        $totalPrice = $pricePerHour * $flightHours;

        $totalPriceFormatted = number_format($totalPrice, 2, '.', '');

        return response()->json([
            'price' => $totalPriceFormatted
        ]);
    }

    public function updateTotalPrice(Request $request)
    {
        $id_flight = $request->input('id_flight');
        $newHours = $request->input('hours');
        $newTotal = $request->input('total');

        $flightHistory = FlightHistory::where('id', $id_flight)->first();
        if(!$flightHistory) {
            return response()->json([
                'error' => 'No se encontró el vuelo correspondiente en flight_history'
            ], 404);
        }
        $flightHistory->update(['hours' => $newHours]);
        FlightPayment::where('id_flight', $id_flight)->update(['total' => $newTotal]);
        return response()->json([
            'msg' => 'Se actualizo el vuelo correctamente'
        ]);
    }

}
