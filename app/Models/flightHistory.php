<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class flightHistory extends Model
{
    use HasFactory;
    protected $table = 'flight_history';
    protected $fillable = [
        'hours',
        'type_flight',
        'flight_date',
        'flight_hour',
        'create_at',
        'update_at'
    ];
}
