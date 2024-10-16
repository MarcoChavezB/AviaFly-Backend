<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecreativeConcept extends Model
{
    use HasFactory;
    protected $filleable = [
        "concept",
        "price",
        "default_hours",
        "description",
        "max_weight",
    ];
}
