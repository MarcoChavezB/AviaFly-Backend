<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CareerController extends Controller
{
    public function create(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:careers,name',
                'monthly_payments' => 'required|integer',
                'registration_fee' => 'required|numeric',
                'monthly_fee' => 'required|numeric',
            ],
            [
                'name.required' => 'El nombre es requerido',
                'name.string' => 'El nombre debe ser una cadena de texto',
                'name.unique' => 'El nombre ya existe',
                'monthly_payments.required' => 'Los pagos mensuales son requeridos',
                'monthly_payments.integer' => 'Los pagos mensuales deben ser un número entero',
                'registration_fee.required' => 'La cuota de inscripción es requerida',
                'registration_fee.numeric' => 'La cuota de inscripción debe ser un número',
                'monthly_fee.required' => 'La cuota mensual es requerida',
                'monthly_fee.numeric' => 'La cuota mensual debe ser un número',
            ]);

            if ($validator->fails()) {
                return response()->json(["errors" => $validator->errors()], 400);
            }

            $career = new Career();
            $career->name = $request->name;
            $career->monthly_payments = $request->monthly_payments;
            $career->registration_fee = $request->registration_fee;
            $career->monthly_fee = $request->monthly_fee;
            $career->save();

            return response()->json(["message" => "ok"], 201);
    }

    public function getCareers()
    {
        try {
                $careers = Career::get(['id', 'name']);
            if ($careers->isEmpty()) {
                return response()->json(["errors" => ["No hay formaciones creadas"]], 404);
            }
            return response()->json(['careers' => $careers], 200);
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

    public function index()
    {
        $careers = Career::all();
        if ($careers->isEmpty()) {
            return response()->json(["errors" => ["No hay carreras creadas"]], 404);
        }
        return response()->json(["careers" => $careers], 200);
    }

    public function update(Request $request)
    {
        $career = Career::find($request->id);
        if (!$career) {
            return response()->json(["errors" => ["La carrera no existe"]], 404);
        }

        $career->monthly_payments = $request->monthly_payments;
        $career->registration_fee = $request->registration_fee;
        $career->monthly_fee = $request->monthly_fee;
        $career->save();
        return response()->json($career, 200);
    }



}
