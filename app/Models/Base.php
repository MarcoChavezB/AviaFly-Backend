<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location'
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'id_base', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_base', 'id');
    }

    public function Employees()
    {
        return $this->hasMany(Employee::class, 'id_base', 'id');
    }
}
