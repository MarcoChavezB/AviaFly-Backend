<?php

namespace App\Http\Controllers;

use App\Models\InstructorLocation;
use Illuminate\Http\Request;

class InstructorLocationController extends Controller
{

    public function storeCurrentLocation(Request $request){
        $data = $request->validate([
            'instructor_id' => 'required|integer|exists:employees,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $location = InstructorLocation::create([
            'instructor_id' => $data['instructor_id'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timestamp' => now(),
        ]);
        return response()->json(['message' => 'Location stored successfully', 'location' => $location], 201);
    }

    public function getInstructorsLocation(){
        $locations = InstructorLocation::with('instructor')
            ->select('instructor_id', 'latitude', 'longitude', 'timestamp')
            ->groupBy('instructor_id', 'latitude', 'longitude', 'timestamp')
            ->orderBy('timestamp', 'asc')
            ->get();

        return response()->json($locations);
    }
}
