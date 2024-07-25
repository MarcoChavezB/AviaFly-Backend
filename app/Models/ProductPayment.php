<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_order',
        'id_payment_method',
        'amount'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }
}
