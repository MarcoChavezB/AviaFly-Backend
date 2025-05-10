<?php

namespace App\Http\Controllers;

use App\Mail\NotifyStudentMail;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NotifyController extends Controller
{
    public function notifyStudent(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'direct_to' => 'required',
            'file' => 'nullable|file|mimes:pdf,docx,jpg,jpeg,png,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fileContent = null;
        $fileName = null;
        $fileMime = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileContent = file_get_contents($file->getRealPath());
            $fileName = $file->getClientOriginalName();
            $fileMime = $file->getMimeType();
        }

        $email = Student::where('id', $request->direct_to)->value('email');

        Mail::to($email)->send(new NotifyStudentMail(
            $request->title,
            $request->content,
            $fileContent,
            $fileName,
            $fileMime
        ));

        return response()->json(['message' => 'Notificación enviada correctamente']);
    }
}
