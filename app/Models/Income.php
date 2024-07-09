<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_date',
        'iva',
        'discount',
        'total',
        'original_import',
        'concept',
        'income_details_id',
    ];
}
