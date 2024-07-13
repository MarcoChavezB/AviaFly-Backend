<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArchiveController extends Controller
{
    public function uploadFile(Request $request, string $file_path)
    {
        $validator = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        if (!$request->file('file')->isValid()) {
            return false;
        }

        $file = $request->file('file');

        $file_name = time() . '_' . $file->getClientOriginalName();

        if (Storage::putFileAs($file_path, $file, $file_name)) {
            return true;
        } else {
            return false;
        }
    }
}
