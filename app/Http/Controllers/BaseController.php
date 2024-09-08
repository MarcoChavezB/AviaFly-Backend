<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Career;
use App\Models\Turn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'location' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $base = new Base();
            $base->name = $request->name;
            $base->location = $request->location;
            $base->save();

            return response()->json($base, 201);
        }catch(\Exception $e){
            return response()->json(["message" => "Internal Server Error"], 500);
        }
    }

    public function getBases()
    {
        try {
            $bases = Base::get(['id', 'name']);

            if($bases->isEmpty()){
                return response()->json(["errors" => ["No hay bases creadas"]], 404);
            }

            return response()->json($bases, 200);
        }catch (\Exception $e) {
            return response()->json(["message" => "Internal Server Error"], 500);
        }
    }
    public function getBasesWithCareersAndTurns()
    {
        try {
            $bases = Base::get(['id', 'name']);

            if($bases->isEmpty()){
                return response()->json(["errors" => ["No hay bases creadas"]], 404);
            }

            $careers = Career::get(['id', 'name']);

           if ($careers->isEmpty()) {
                return response()->json(["errors" => ["No hay carreras creadas"]], 404);
            }

           $turns = Turn::get(['id', 'name']);

           if ($turns->isEmpty()) {
               return response()->json(["errors" => ["No hay turnos creados"]], 404);
           }

            return response()->json(['bases' => $bases, 'careers' => $careers, 'turns' => $turns], 200);
        }catch (\Exception $e) {
            return response()->json(["message" => "Internal Server Error"], 500);
        }
    }
}
