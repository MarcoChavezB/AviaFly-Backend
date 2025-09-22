<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirplaneOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'airplane_id',
        'airplane_use_id'
    ];

    public function airplane(){
        return $this->belongsTo(AirPlane::class, 'airplane_id');
    }

    public function airplane_uses(){
        return $this->belongsTo(AirplaneUse::class, 'airplane_use_id');
    }
}
