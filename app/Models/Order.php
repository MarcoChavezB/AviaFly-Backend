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
        'id_employe',
        'id_customer'
    ];

    public function employe()
    {
        return $this->belongsTo(User::class, 'id_employ');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'id_customer');
    }
}
