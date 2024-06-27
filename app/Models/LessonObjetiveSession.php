<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonObjetiveSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_flight_objetive',
        'id_lesson',
        'id_session',
    ];

    public function flightObjetive()
    {
        return $this->belongsTo(FlightObjetive::class, 'id_flight');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'id_lesson');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'id_session');
    }
}
