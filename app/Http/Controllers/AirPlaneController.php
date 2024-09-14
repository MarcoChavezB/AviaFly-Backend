<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use Illuminate\Http\Request;

class AirPlaneController extends Controller
{

    public function resetHours(){
        $airplane = AirPlane::first();
        $airplane->tacometer = 0;
        $airplane->save();

        return response()->json(['message' => 'Hours reseted']);
    }

    public function totalWeightExceded($id_airplane, $total_weight){
        $airplane = AirPlane::find($id_airplane);
        return intval($airplane->limit_weight) < intval($total_weight);
    }

    public function getTotalWeight($id_airplane)
    {
        $airplane = AirPlane::find($id_airplane);
        return $airplane->limit_weight;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $aitplanes = AirPlane::select('id', 'model', 'limit_hours')->get();
        return response()->json($aitplanes);
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
     * @param  \App\Models\AirPlane  $airPlane
     * @return \Illuminate\Http\Response
     */
    public function show(AirPlane $airPlane)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AirPlane  $airPlane
     * @return \Illuminate\Http\Response
     */
    public function edit(AirPlane $airPlane)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AirPlane  $airPlane
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AirPlane $airPlane)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AirPlane  $airPlane
     * @return \Illuminate\Http\Response
     */
    public function destroy(AirPlane $airPlane)
    {
        //
    }
}
