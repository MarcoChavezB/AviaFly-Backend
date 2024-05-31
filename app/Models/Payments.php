<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $fillable = ['amount', 'id_flight', 'pay_method'];
    
    function flight(){
        return $this->belongsTo(FlightPayment::class, 'id_flight');
    }
}
