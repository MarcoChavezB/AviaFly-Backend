<?php

namespace App\Http\Controllers;

use App\Mail\ReportIssue;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    /*
     * payload:
     * {
     *    'user_identification': "AT1",
     *    'systemUrl': "http..."
     *    'reportDescription': "...."
     * }
     */
    function notifyErrorFromStudent(Request $request) {
        // Validación de los datos de entrada
        $validator = Validator::make($request->all(), [
            'user_identification' => 'required|string',
            'systemUrl' => 'required|url',
            'reportDescription' => 'required|string|max:500',
        ], [
            'user_identification.required' => 'La identificación del usuario es obligatoria.',
            'user_identification.string' => 'La identificación del usuario debe ser un texto.',
            'systemUrl.required' => 'La URL es obligatoria.',
            'systemUrl.url' => 'Por favor, ingrese una URL válida.',
            'reportDescription.required' => 'La descripción es obligatoria.',
            'reportDescription.string' => 'La descripción debe ser un texto.',
            'reportDescription.max' => 'La descripción no puede tener más de 500 caracteres.',
        ]);

        // Si la validación falla, devuelve errores
        if ($validator->fails()) {
            return response()->json(['resp' => $validator->errors()], 422);
        }

        // Buscar al estudiante por identificación
        $student = Student::where('user_identification', $request->user_identification)->first();

        // Verificar si el estudiante fue encontrado
        if (!$student) {
            return response()->json(['resp' => 'El estudiante no fue encontrado.'], 404);
        }

        // Si el estudiante existe, envía el correo
        Mail::to(env('TICS_SUPPORT_EMAIL', 'avia2024trc@gmail.com'))->send(new ReportIssue($student, $request->systemUrl, $request->reportDescription));

        return response()->json(['resp' => "El Reporte fue enviado"]);
    }
}
