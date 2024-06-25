<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightCustomer extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_employee',
        'name',
        'email',
        'phone',
        'flight_type',
        'flight_hours',
        'reservation_hour',
        'reservation_date',
        'payment_status',
        'payment_method',
        'flight_status',
        'total'
    ];
}
