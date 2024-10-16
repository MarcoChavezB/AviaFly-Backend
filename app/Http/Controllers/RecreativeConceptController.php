<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\RecreativeConcept;
use Illuminate\Http\Request;

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
    public function destroy(RecreativeConcept $recreativeConcept)
    {
        //
    }
}
