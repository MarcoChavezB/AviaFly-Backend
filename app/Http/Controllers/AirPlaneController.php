<?php

namespace App\Http\Controllers;

use App\Models\AirPlane;
use App\Models\AirplaneUse;
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
    public function getIndexData()
    {
        $airplanes = Airplane::all();
        return response()->json($airplanes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @Request: {
            "model": "Modelo ",
            "limitHours": "10",
            "limitWeight": "100",
            "limitPassengers": "10",
            "options": {
                "recreative": true,
                "academic": true
            },
            "file": {}
        }
     */
    public function store(Request $request)
    {
        // Validar los datos básicos
        $validated = $request->validate([
            'model' => 'required|string',
            'limitHours' => 'required|integer',
            'limitWeight' => 'required|string',
            'limitPassengers' => 'required|string',
            'options' => 'required|array',
        ]);

        // Manejar el archivo si viene

        $imagePath = null;
        if ($request->hasFile('file')) {
            $fileController = new FileController();
            $imagePath = $fileController->saveFile($request->file('file'), 1, 'todos', $fileController->getBasePath());
        }


        // Crear el avión
        $airplane = AirPlane::create([
            'model' => $validated['model'],
            'limit_hours' => $validated['limitHours'],
            'limit_weight' => $validated['limitWeight'],
            'limit_passengers' => $validated['limitPassengers'],
            'image_url' => $imagePath,
        ]);

        // Asignar opciones de uso (recreative, academic...)
        foreach ($validated['options'] as $useKey => $enabled) {
            // Buscar el uso por nombre
            $use = AirplaneUse::where('name', $useKey)->first();
            if ($use) {
                $airplane->uses()->attach($use->id, ['enabled' => $enabled]);
            }
        }

        return response()->json(['message' => 'Avión creado correctamente', 'airplane' => $airplane]);
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
