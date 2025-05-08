<?php

namespace App\Http\Controllers;

use App\Models\IncomeOption;
use Illuminate\Http\Request;

class IncomeOptionController extends Controller
{
    function getLastExportIncomeDate(){
        $lastExportIncomeDate = IncomeOption::where('name', 'last_facture_date')->first();

        if(!$lastExportIncomeDate){
            return response()->json([
                'msg' => 'No existe la fecha de la última exportación de ingresos',
                'success' => false
            ], 404);
        }

        return response()->json([
            'msg' => 'La fecha de la última exportación de ingresos',
            'data' => $lastExportIncomeDate->value,
            'success' => true
        ], 200);
    }

    function updateLastExportIncomeDate(){
        $lastExportIncomeDate = IncomeOption::where('name', 'last_facture_date')->first();

        if(!$lastExportIncomeDate){
            return response()->json([
                'msg' => 'No existe la fecha de la última exportación de ingresos',
                'success' => false
            ], 404);
        }

        $lastExportIncomeDate->value = now()->format('Y-m-d');
        $lastExportIncomeDate->save();

        return response()->json([
            'msg' => 'La fecha de la última exportación de ingresos se ha actualizado',
            'data' => $lastExportIncomeDate->value,
            'success' => true
        ], 200);
    }
}
