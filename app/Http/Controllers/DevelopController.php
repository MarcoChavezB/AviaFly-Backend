<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\IncomeDetails;
use Barryvdh\DomPDF\Facade\Pdf;


class DevelopController extends Controller
{
    public function restoreTickets()
    {
        return response()->json(['message' => 'Tickets restaurados con éxito']);
    }

    private function sanitizeName(string $name): string
    {
        $search = ['á', 'é', 'í', 'ó', 'ú'];
        $replace = ['a', 'e', 'i', 'o', 'u'];
        return strtolower(str_replace($search, $replace, $name));
    }

    private function generateFileName(string $baseName, string $userIdentification, string $type, ?string $extension = 'pdf'): string
    {
        $prefix = ($type === 'tickets') ? 'ticket_' : 'voucher_';
        return "bases/{$baseName}/{$userIdentification}/{$type}/{$prefix}" . time() . '.' . $extension;
    }
}
