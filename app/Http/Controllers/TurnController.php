<?php

namespace App\Http\Controllers;

use App\Models\Turn;
use Illuminate\Http\Request;

class TurnController extends Controller
{
    public function index()
    {
        $turns = Turn::all();

        if ($turns->isEmpty()) {
            return response()->json(["errors" => ["No hay turnos creados"]], 404);
        }

        return response()->json($turns, 200);
    }
}
