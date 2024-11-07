<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightHoursRestrictions extends Model
{
    use HasFactory;

    protected $table = 'flight_hours_restrictions';

    protected $fillable = [
        'motive',
        'start_hour',
        'end_hour',
        'description',
        'start_date',
        'end_date',
        'repetitive',
        'id_flight',
    ];


    /**
     * Relación con la tabla `info_flights` para `id_flight`.
     */
    public function flight()
    {
        return $this->belongsTo(InfoFlight::class, 'id_flight');
    }
}
