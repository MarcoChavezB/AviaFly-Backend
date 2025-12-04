<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Student;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class FileController extends Controller
{

    public function generateManualUrl(string $filePath): string
    {
        $domain = 'http://api.aviafly.mx:8080/AviaFly-Backend/public';

        return $domain . '/' . ltrim($filePath, '/');
    }

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

        return $basePath . '/' . strtolower($baseName) . '/' . $directTo . '/' . $this->sanitizeName($originalName) . '_' . time() . '.' . $extension;
    }

    public function saveFile($file, int $baseId, string $directTo, string $basePath): string
    {
        $base = Base::findOrFail($baseId);
        $baseName = $this->sanitizeName($base->name);

        $fileName = $this->generateFileName($file, strtolower($baseName), $directTo, $basePath);
        $directory = public_path(dirname($fileName));

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $file->move($directory, basename($fileName));

        return $this->generateManualUrl($fileName);
    }

    /*
     * @Request: url del archivo a eliminar
     * url http://api.aviafly.site:8080/AviaFly-Backend/public/bases/torreon/AT37/tickets/ticket_1728107494.pdf
    *  */
    function deleteFile(string $url): bool
    {
        $urlDomain = "http://api.aviafly.mx:8080/AviaFly-Backend/public";
        $onlyUrl = str_replace($urlDomain, '', $url);
        $filePath = ltrim($onlyUrl, '/');
        $fullPath = public_path($filePath);

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

        return $this->generateManualUrl($fileName);
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

        return $this->generateManualUrl($filePath);
    }

    public function saveBase64File(string $base64, string $fileName): string
    {
        // Decodificar el Base64
        $fileData = base64_decode($base64);

        if ($fileData === false) {
            throw new \Exception('El contenido proporcionado no es un Base64 válido.');
        }

        $directory = public_path('reports');

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filePath = 'reports/' . $fileName;

        File::put(public_path($filePath), $fileData);

        return $this->generateManualUrl($filePath);
    }

}
