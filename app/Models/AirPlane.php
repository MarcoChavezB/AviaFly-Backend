<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirPlane extends Model
{
    use HasFactory;
    protected $fillable = [
        'model',
        'limit_hours'
    ];
}
