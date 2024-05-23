<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'id_user',
        'id_career'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id');
    }

}
