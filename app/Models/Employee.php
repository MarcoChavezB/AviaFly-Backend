<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'last_names',
        'email',
        'company_email',
        'phone',
        'cellphone',
        'curp',
        'user_identification',
        'user_type',
        'id_base',
        'notify_sale'
    ];

    public function base()
    {
        return $this->belongsTo(Base::class, 'id_base', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_identification', 'user_identification');
    }
}
