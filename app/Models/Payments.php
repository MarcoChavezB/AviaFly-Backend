<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $fillable = ['amount', 'id_flight', 'id_payment_method'];

    function flight(){
        return $this->belongsTo(FlightPayment::class, 'id_flight');
    }

    function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }
}
