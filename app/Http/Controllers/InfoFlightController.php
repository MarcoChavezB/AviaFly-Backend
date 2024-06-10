<?php

namespace App\Http\Controllers;

use App\Models\flightHistory;
use App\Models\InfoFlight;

class InfoFlightController extends Controller
{
    public function index()
    {
        return InfoFlight::all();
    }
    
    function getEquipFlight(){
        $equipoValues = flightHistory::getEnumValues('equipo');
        return response()->json($equipoValues);
    }
    
    function getFlightType(){
        $flightTypeValues = flightHistory::getEnumValues('type_flight');
        return response()->json($flightTypeValues);
    }
    
    function getFlightCategory(){
        $flightCategoryValues = flightHistory::getEnumValues('flight_category');
        return response()->json($flightCategoryValues);
    }
    
    function getFlightManeuver(){
        $maneuverValues = flightHistory::getEnumValues('maneuver');
        return response()->json($maneuverValues);
    }
}
