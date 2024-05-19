<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_names',
        'email',
        'company_email',
        'phone',
        'cellphone',
        'curp',
        'user_identification',
        'user_type',
        'id_base',
    ];
}
