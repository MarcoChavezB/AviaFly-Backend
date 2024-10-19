<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $filleable = [
        'amount',
        'payment_voucher',
        'payment_ticket',
        'id_payment_method',
        'id_customer_flight',
    ];


    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

    public function customerFlight(){
        return $this->belongsTo(FlightCustomer::class, 'id_customer_flight');
    }
}
