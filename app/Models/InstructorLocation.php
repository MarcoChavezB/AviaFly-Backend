<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorLocation extends Model
{
    use HasFactory;

    public $table = 'instructor_locations';
    protected $fillable = [
        'instructor_id',
        'latitude',
        'longitude',
        'timestamp'
    ];

    public $timestamps = false;
    public function instructor()
    {
        return $this->belongsTo(Employee::class, 'instructor_id');
    }
}
