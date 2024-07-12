<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewSletterStudentsEmployee extends Model
{
    use HasFactory;
    protected $filleable = ['id_new_sletter', 'id_student', 'id_employee', 'is_read'];

    public function newSletter()
    {
        return $this->belongsTo(NewSletter::class, 'id_new_sletter');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }
}
