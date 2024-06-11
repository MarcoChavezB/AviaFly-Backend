<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPayment extends Model
{
use HasFactory;

    protected $fillable = [
        'id_student',
        'id_flight',
        'id_instructor',
        'id_employee',
        'total',
        'payment_status',
        'due_week',
        'installment_value',
        'instructor_cost',
        'created_at',
        'updated_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }

    public function flight()
    {
        return $this->belongsTo(FlightHistory::class, 'id_flight');
    }

    public function instructor()
    {
        return $this->belongsTo(Employee::class, 'id_instructor');
    }
}
