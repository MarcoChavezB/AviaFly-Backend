<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirplaneUse extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

public function airplanes()
{
    return $this->belongsToMany(AirPlane::class, 'airplane_options', 'airplane_use_id', 'airplane_id')
                ->withPivot('enabled')
                ->withTimestamps();
}
}
