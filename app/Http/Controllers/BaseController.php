<?php

namespace App\Http\Controllers;

use App\Models\Base;
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
}
