<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseStudent extends Model
{
    use HasFactory;
    protected $fillable = [
        'calification',
        'id_course',
        'id_student'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'id_student');
    }
}
