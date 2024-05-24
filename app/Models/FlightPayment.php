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
        'id_instructor',
        'total',
        'status',
        'payment_method',
        'dueWeek',
        'intallment_value',
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

    public function id_instructor(){
        return $this->belongsTo(Employee::class, 'id_instructor')
    }

}
