<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    function generatePDF(){
        $data = [
            'title' => 'Welcome to LaravelTuts.com',
            'date' => date('m/d/Y'),
        ];
                $pdf = PDF::loadView('flightReservationTiket', $data);

        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="flightReservationTiket.pdf"');

    }
}
