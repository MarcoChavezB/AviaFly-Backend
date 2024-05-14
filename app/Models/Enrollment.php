<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'id_user',
        'id_career'
    ];
    
        function user(){
        return $this->hasMany(User::class, 'is_user');
    }
    
    function carrier(){
        return $this->hasMany(Career::class, 'id:carrier');
    }
}
