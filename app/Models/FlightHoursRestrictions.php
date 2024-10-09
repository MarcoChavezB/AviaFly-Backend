<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightHoursRestrictions extends Model
{
    use HasFactory;
    protected $fillable = [
        'motive',
        'start_date',
        'end_date',
        'start_hour',
        'end_hour',
        'description',
        'id_flight',
    ];


    public function flight(){
        return $this->belongsTo(InfoFlight::class, 'id_flight');
    }
}
