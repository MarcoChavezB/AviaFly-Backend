<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLicense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_employee',
        'id_license',
        'expiration_date',
        'license_date',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }

    public function license()
    {
        return $this->belongsTo(License::class, 'id_license')->withTrashed();
    }
}
