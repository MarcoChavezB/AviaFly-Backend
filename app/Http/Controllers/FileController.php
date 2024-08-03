<?php

namespace App\Http\Controllers;

use App\Models\Base;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class FileController extends Controller
{
    protected $basePath = 'newsletters/bases';
    public function getBasePath(): string
    {
        return $this->basePath;
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
}

