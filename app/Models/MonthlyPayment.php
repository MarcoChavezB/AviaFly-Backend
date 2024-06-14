<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_student',
        'status',
        'payment_date',
        'amount',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }
}
