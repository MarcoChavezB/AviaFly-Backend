<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightCustomer extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'flight_hours',
        'reservation_date',
        'reservation_hour',
        'weight',
        'number_of_passengers',
        'payment_status',
        'flight_status',
        'total',
        'id_employee',
        'id_flight',
        'id_payment_method'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }

    public function flight()
    {
        return $this->belongsTo(InfoFlight::class, 'id_flight');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }
}
