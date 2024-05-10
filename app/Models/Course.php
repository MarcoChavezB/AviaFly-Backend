<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_date',
        'finish_date',
        'id_subject',
        'id_teacher'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'id_teacher');
    }
}
