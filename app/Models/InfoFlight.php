<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoFlight extends Model
{
    use HasFactory;
    protected $fillable = ['flight_type', 'price'];
}
