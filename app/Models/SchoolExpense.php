<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolExpense extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'motive',
        'date',
        'invoice_path',
        'status',
        'amount',
        'created_by',
        'payment_method',
        'approved_by',
    ];

    /**
     * Relación: SchoolExpense pertenece a un empleado (quien lo creó).
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Relación: SchoolExpense tiene un método de pago.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    /**
     * Relación: SchoolExpense fue aprobado por un empleado.
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
