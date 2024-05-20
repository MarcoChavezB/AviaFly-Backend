<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'monthly_payments',
        'registration_fee',
        'monthly_fee',
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'career_subjects', 'id_career', 'id_subject');
    }
}
