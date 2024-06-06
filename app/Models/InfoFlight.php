<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoFlight extends Model
{
    use HasFactory;
    protected $fillable = [
    'flight_type', 
    'price', 
    'min_credit_hours_required',
    'min_hours_required'
    ];
}
