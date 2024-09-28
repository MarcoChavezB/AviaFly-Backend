<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInRecords extends Model
{
    use HasFactory;

    protected $fillable = [
        'arrival_date',
        'arrival_time',
        'id_employee',
        'type',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }
}
