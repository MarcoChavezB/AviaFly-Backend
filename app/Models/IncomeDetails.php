<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'student_id',
        'commission',
        'payment_method',
        'bank_account',
        'file_path',
        'ticket_path',
    ];
}
