<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubjectTurn extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_teacher',
        'career_subject_id',
        'id_turn',
        'start_date',
        'end_date',
        'duration',
    ];

    function subject(){
        return $this->belongsTo(Subject::class);
    }

    function teacher(){
        return $this->belongsTo(Employee::class);
    }

    function turn(){
        return $this->belongsTo(Turn::class);
    }
}
