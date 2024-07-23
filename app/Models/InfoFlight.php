<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoFlight extends Model
{
    use HasFactory;
    protected $fillable = [
        'equipo',
        'price',
        'min_credit_hours_required',
        'min_hours_required',
        'max_weight'
    ];
}
