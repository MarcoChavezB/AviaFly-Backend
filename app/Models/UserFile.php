<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_file',
        'file_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function file(){
        return $this->belongsTo(AcademicFile::class, 'id_file');
    }
}
