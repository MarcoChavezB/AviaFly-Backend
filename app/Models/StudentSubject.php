<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_student',
        'id_subject',
        'id_teacher',
        'id_turn',
        'final_grade',
        'status'
    ];


    public function student(){
        return $this->belongsTo(Student::class, 'id_student');
    }
}
