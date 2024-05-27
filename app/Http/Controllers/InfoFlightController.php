<?php

namespace App\Http\Controllers;

use App\Models\InfoFlight;
use Illuminate\Http\Request;

class InfoFlightController extends Controller
{
    public function index()
    {
        return InfoFlight::all();
    }
}
