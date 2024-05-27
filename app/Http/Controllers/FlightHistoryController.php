<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\FlightPayment;
use Illuminate\Http\Request;

class FlightHistoryController extends Controller
{
function isDateReserved(Request $request){
    $data = $request->all();
    $date = $data['date'];
    $id_instructor = $data['id_instructor'];
    $flight_type = $data['flight_type'];
    
    

}


}
