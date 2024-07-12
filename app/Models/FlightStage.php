<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_flight',
        'id_stage',
    ];
    function flight() {
        return $this->belongsTo(flightHistory::class, 'id_flight');
    }
    function stage() {
        return $this->belongsTo(Stage::class, 'id_stage');
    }
}
