<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AcademicFileController extends Controller
{
public function index($id_student){
    $files = UserFile::select(
            'academic_files.id',
            'academic_files.file_name',
            'user_files.file_path',
            'section_files.section_name',
            'section_files.id as id_section'
        )
        ->leftJoin('academic_files', 'academic_files.id', '=', 'user_files.id_file')
        ->leftJoin('section_files', 'section_files.id', '=', 'academic_files.id_section_file')
        ->where('user_files.id_user', $id_student)
        ->get();

    $groupedFiles = $files->groupBy('section_name')->map(function ($items, $section) {
        return [
            'id_section' => $items[0]->id_section,
            'section' => $section,
            'files' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'file_name' => $item->file_name,
                    'file_path' => $item->file_path
                ];
            })->values()
        ];
    })->values();

    return response()->json($groupedFiles);
}


/* {
    "id_student": "1",
    "files": [
        {
            "id_file": "3",
            "file": {}
        }
    ]
} */
    /* public function store(Request $request)
    {
        $data = $request->all();
        $id_student = $data['id_student'];
        $files = $data['files'];
        $filecontroller = new FileController();

        $student = Student::find($id_student);

        foreach ($files as $file) {
            $userfile = UserFile::where('id', $file['id_file'])
                                ->where('id_user', $id_student)
                                ->first();

            if($userfile && $userfile->file_path){
                $filecontroller->deleteFile($userfile->file_path);
            }

            if ($userfile && isset($file['file'])) {
                $url = $filecontroller->saveFilePath($file['file'], $student->id_base, "academic_files", $student);
                $userfile->file_path = $url;
                $userfile->save();
            }
        }

        return response()->json(['message' => 'Files saved successfully', "debug" => $data, "url" => $userfile]);
    } */

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_student' => 'required|integer',
            'files' => 'required|array',
            'files.*.id_file' => 'required|integer',
            'files.*.file' => 'nullable|file' // Asegúrate de validar el archivo si es necesario
        ]);

        $id_student = $data['id_student'];
        $files = $data['files'];
        $fileController = new FileController();

        $student = Student::findOrFail($id_student); // Esto lanzará una excepción si no se encuentra el estudiante

        foreach ($files as $file) {
            $userfile = UserFile::where('id_file', $file['id_file'])
                                ->where('id_user', $id_student)
                                ->first();

            if ($userfile && $userfile->file_path) {
                $fileController->deleteFile($userfile->file_path);
            }

            $url = $fileController->saveFilePath($file['file'], $student->id_base, "academic_files", $student);
            $userfile->file_path = $url;
            $userfile->save();
        }

        return response()->json(['message' => 'Files saved successfully', "debug" => $userfile]);
    }
}

