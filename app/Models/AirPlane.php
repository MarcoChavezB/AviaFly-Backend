<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirPlane extends Model
{
    use HasFactory;
    protected $fillable = [
        'model',
        'limit_hours',
        'limit_weight',
        'limit_passengers',
        'image_url',
    ];

public function uses()
{
    return $this->belongsToMany(AirplaneUse::class, 'airplane_options', 'airplane_id', 'airplane_use_id')
                ->withPivot('enabled')
                ->withTimestamps();
}

}
