<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Career;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'last_names',
        'email',
        'phone',
        'cellphone',
        'curp',
        'credit',
        'flight_credit',
        'user_identification',
        'emergency_contact',
        'emergency_phone',
        'emergency_direction',
        'id_created_by',
        'id_base',
        'id_history_flight',
        'id_career',
        'start_date',
        'simulator_credit',
        'afac_user',
        'afac_password',
        'afac_emission',
        'afac_expiration'
    ];

    public function career()
    {
        return $this->belongsTo(Career::class, 'id_career');
    }

    public function base()
    {
        return $this->belongsTo(Base::class, 'id_base');
    }

    public function monthly_payments()
    {
        return $this->hasMany(MonthlyPayment::class, 'id_student');
    }

    public function owed_and_pending_payments()
    {
        return $this->hasMany(MonthlyPayment::class, 'id_student')
            ->whereIn('status', ['owed', 'pending']);
    }

}
