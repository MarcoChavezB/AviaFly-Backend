<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeConcept extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'value',
        'view_value'
    ];
}
