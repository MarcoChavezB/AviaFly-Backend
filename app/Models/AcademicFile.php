<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_section_file',
        'file_name',
    ];

    public function sectionFile()
    {
        return $this->belongsTo(sectionFile::class, 'id_section_file');
    }
}
