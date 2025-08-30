<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payment_date',
        'student_id',
        'commission',
        'payment_method',
        'bank_account',
        'file_path',
        'ticket_path',
        'total',
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class, 'income_details_id');
    }
}
