<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Student;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class FileController extends Controller
{
    protected $basePath = 'newsletters/bases';
    protected $tiketPath = 'bases';

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getTicketPath(): string
    {
        return $this->tiketPath;
    }


    private function sanitizeName(string $name): string
    {
        $search = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', ' '];
        $replace = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', '_'];

        return str_replace($search, $replace, $name);
    }

    private function generateFileName(UploadedFile $file, string $baseName, string $directTo, string $basePath): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        return $basePath . '/' . $baseName . '/' . $directTo . '/' . $this->sanitizeName($originalName) . '_' . time() . '.' . $extension;
    }

    public function saveFile($file, int $baseId, string $directTo, string $basePath): string
    {
        $base = Base::findOrFail($baseId);
        $baseName = $this->sanitizeName($base->name);

        $fileName = $this->generateFileName($file, $baseName, $directTo, $basePath);
        $directory = public_path(dirname($fileName));

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file->move($directory, basename($fileName));

        return url($fileName);
    }

    /*
     * @Request: url del archivo a eliminar
    *  */
    function deleteFile(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }


    private function generateTicketName(string $originalName, string $baseName, string $basePath, Student $student): string
    {
        $extension = 'pdf';
        $originalName = $this->sanitizeName($originalName);
        return $basePath . '/' . $baseName . '/' . $student->user_identification . '/tickets/' . $this->sanitizeName($originalName) . '_' . time() . '.' . $extension;
    }

    public function saveTicket(PDF $pdf, Student $student, int $baseId): string
    {
        $base = Base::findOrFail($baseId);
        $baseName = $this->sanitizeName($base->name);

        $originalName = 'ticket';
        $fileName = $this->generateTicketName($originalName, $baseName, $this->tiketPath, $student);
        $directory = public_path(dirname($fileName));

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $pdf->save($directory . '/' . basename($fileName));

        return url($fileName);
    }


    public function saveFilePath($file, int $baseId, string $path, Student $student): string
    {
        $base = Base::findOrFail($baseId);
        $baseName = $this->sanitizeName($base->name);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        $uniqueName = $originalName . '_' . time() . '.' . $extension;

        $filePath = 'bases/' . $baseName . '/' . $student->user_identification . '/' . $path . '/' . $uniqueName;

        $file->move(public_path(dirname($filePath)), $uniqueName);

        return url($filePath);
    }

}
