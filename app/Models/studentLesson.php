<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class studentLesson extends Model
{
    use HasFactory;
    protected $fillable = ['id_student', 'id_lesson', 'passed'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'id_lesson');
    }
}
