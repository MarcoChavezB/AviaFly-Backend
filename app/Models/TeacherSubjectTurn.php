<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubjectTurn extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_teacher',
        'id_subject',
        'id_turn',
        'start_date',
        'end_date',
        'duration',
    ];
}
