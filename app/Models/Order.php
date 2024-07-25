<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_date',
        'total',
        'sub_total',
        'due_week',
        'installment_value',
        'payment_status',
        'id_employe',
        'id_discount',
        'id_client',
        'id_payment_method'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employe');
    }

    public function client()
    {
        return $this->belongsTo(Student::class, 'id_client');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_details', 'id_order', 'id_product')
            ->withPivot('quantity');
    }

    public function productPayments()
    {
        return $this->hasMany(ProductPayment::class, 'id_order');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }
}
