<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPayment extends Model
{
    use HasFactory;

    protected $filleable =
    [
        'id_student',
        'id_flight',
        'total',
        'status',
        'paymentMethod',
        'dueWeek',
        'intallmentValue',
        'created_at',
        'updated_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function flight()
    {
        return $this->belongsTo(Flight::class, 'id_flight');
    }

}
