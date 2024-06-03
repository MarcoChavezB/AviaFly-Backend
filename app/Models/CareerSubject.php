<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerSubject extends Model
{
    use HasFactory;

    protected $table = 'career_subjects';

    protected $fillable = [
        'id_career',
        'id_subject',
    ];
}
