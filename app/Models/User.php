<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'user_identification',
        'photo',
        'phone',
        'cellphone',
        'curp',
        'email',
        'company_email',
        'emergency_contact',
        'emergency_phone',
        'emergency_direction',
        'user_type',
        'password',
        'credit',
        'id_created_by',
        'id_base',
        'id_carrier'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function bases()
    {
        return $this->hasMany(Base::class, 'id_base');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_created_by');
    }

    public function careers()
    {
        return $this->hasMany(Career::class, 'id_carrier');
    }
}
