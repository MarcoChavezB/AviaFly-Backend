<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumableFlight extends Model
{
    use HasFactory;
    protected $fillable =
['id_consumable', 'id_flight', 'date', 'liters', 'comments'];

    public function consumable()
    {
        return $this->belongsTo(Consumable::class, 'id_consumable');
    }

    public function flight()
    {
        return $this->belongsTo(FlightHistory::class, 'id_flight');
    }
}
