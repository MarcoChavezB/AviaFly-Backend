<?php

namespace App\Http\Controllers;

use App\Models\ServicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServicePaymentController extends Controller
{
    function index(){
        $servicePayment = ServicePayment::all();
        if($servicePayment->isEmpty()){
            return response()->json([
                'message' => 'no hay servicios registrados'
            ]);
        }

        return response()->json($servicePayment);
    }

    function store(Request $request){
        $data = $request->all();

        $validator = Validator::make($data, [
            'type' => 'required|min:3|max:255',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                "errors" => $validator->errors()
            ]);
        }

        ServicePayment::create([
            'type' => $data['type'],
            'date' => $data['date'],
            'is_done' => false
        ]);

        return response()->json(['message' => 'se creo el servicio correctamente']);
    }

    function toggleServiceStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:service_payments,id',
            'is_done' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        $service = ServicePayment::find($data['id']);

        $service->is_done = $data['is_done'];
        $service->save();

        return response()->json(['message' => 'El estado del servicio se actualizó correctamente']);
    }

    function deleteService($id_service){
        $service = ServicePayment::find($id_service);
        $service->delete();

        return response()->json(['message' => 'se elimino el servicio']);
    }



}
