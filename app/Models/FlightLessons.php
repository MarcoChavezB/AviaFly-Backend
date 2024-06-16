<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightLessons extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_fligth',
        'id_lesson',
        'lesson_approved'
    ];

    public function flightHistory()
    {
        return $this->belongsTo(FlightHistory::class, 'id_flight');
    }

    public function lesson(){
        return $this->belongsTo(Lesson::class, 'id_lesson');
    }
}
