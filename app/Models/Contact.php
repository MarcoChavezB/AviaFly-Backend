<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_names',
        'email',
        'secondary_email',
        'phone',
        'cellphone',
        'company',
        'giro',
        'curp',
        'street',
        'outside_number',
        'inside_number',
        'neighborhood',
        'municipality',
        'zip_code',
        'state',
        'country',
        'feedback',
    ];
}
