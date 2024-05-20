<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_names',
        'email',
        'phone',
        'cellphone',
        'curp',
        'credit',
        'user_identification',
        'emergency_contact',
        'emergency_phone',
        'emergency_direction',
        'id_created_by',
        'id_base',
        'id_history_flight',
        'id_career',
        'start_date',
    ];
}
