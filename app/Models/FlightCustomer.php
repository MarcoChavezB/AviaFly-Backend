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
        'id_payment_method',
        'id_pilot',
        'id_air_planes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }

    public function info_flight()
    {
        return $this->belongsTo(InfoFlight::class, 'id_flight');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

    public function pilot(){
        return $this->belongsTo(Employee::class, 'id_pilot');
    }

    public function airplane(){
        return $this->belongsTo(AirPlane::class, 'id_air_planes');
    }
}




/*
 *      id_flight_type: ['1', [Validators.required]],
      id_pilot: [1, [Validators.required]],
      id_airplane: [0],

      flight_hours: [1, [Validators.required, Validators.min(1), Validators.max(10)]],
      flight_passengers: [1, [Validators.required, Validators.min(1), Validators.max(4)]],
      flight_reservation_date: ['', [Validators.required]],
      flight_reservation_hour: ['', [Validators.required]],
      payment_method: [1, [Validators.required]],
      total_price: [0, [Validators.required]],

      first_passenger_weight: [0, [Validators.required, Validators.min(1), Validators.max(2300)]],
      first_passenger_name: ['', [Validators.required]],
      first_passenger_age: [0, [Validators.required]],

      second_passenger_weight: [0],
      second_passenger_name: [''],
      second_passenger_age: [0],

      tird_passenger_weight: [0],
      tird_passenger_name: [''],
      tird_passenger_age: [0],

      pilot_weight: [0, [Validators.required, Validators.min(1), Validators.max(2300)]],

      total_weight: [0, [Validators.max(2400)]]
 *
 * */
