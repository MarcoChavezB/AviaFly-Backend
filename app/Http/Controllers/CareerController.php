<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:careers,name',
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $career = new Career();
            $career->name = $request->name;
            $career->save();

            return response()->json($career, 201);
        } catch (\Exception $e) {
            return response()->json(["message" => "Internal Server Error"], 500);
        }
    }

    public function getCareers()
    {
        try {
            $careers = Career::all();
            if ($careers->isEmpty()) {
                return response()->json(["errors" => ["No hay formaciones creadas"]], 404);
            }
            return response()->json($careers, 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "Internal Server Error"], 500);
        }
    }


    public function getCareersWithSubjects()
    {
        try {
            $careers = Career::with('subjects:id,name')->select('id', 'name')->get();
            if ($careers->isEmpty()) {
                return response()->json(["errors" => ["No hay carreras creadas"]], 404);
            }
            return response()->json($careers, 200);
        } catch (\Exception $e) {
            return response()->json(["message" => ["Internal Server Error", $e->getMessage()]], 500);
        }
    }



}
