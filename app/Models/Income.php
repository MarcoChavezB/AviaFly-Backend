<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'employee_id',
        'payment_date',
        'iva',
        'discount',
        'total',
        'commission',
        'payment_method',
        'bank_account',
        'original_import',
        'file_path',
        'ticket_path',
        'concept'
    ];
}
