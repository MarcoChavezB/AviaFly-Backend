<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Day;

class RestrictionDay extends Model
{
    use HasFactory;
    protected $filleable = [
        "id_day",
        "id_flight_restriction"
    ];

    public function Day(){
        return $this->belongsTo(Day::class, 'id_day');
    }


    public function FlightRestriction(){
        return $this->belongsTo(FlightHoursRestrictions::class, 'id_flight_restriction');
    }
}
