<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    public function getLocation(Request $request)
    {
        // Reemplaza con tu clave API de Google
        $apiKey = "AIzaSyAdnnJJkGpFgcoYG_2rz75pvv8X_gG2fow";

        try {
            // Realiza la solicitud POST a la API de Google Geolocation
            $response = Http::post("https://www.googleapis.com/geolocation/v1/geolocate?key={$apiKey}", [
                'homeMobileCountryCode' => $request->input('homeMobileCountryCode', 310),
                'homeMobileNetworkCode' => $request->input('homeMobileNetworkCode', 410),
                'radioType' => $request->input('radioType', 'gsm'),
                'carrier' => $request->input('carrier', 'Vodafone'),
                'considerIp' => $request->input('considerIp', true)
            ]);

            // Devuelve la respuesta de Google al cliente Angular
            return response()->json($response->json(), $response->status());

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la ubicación'], 500);
        }
    }
}
