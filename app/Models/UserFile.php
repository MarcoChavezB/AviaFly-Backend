<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificado_preparatoria',
        'acta_nacimiento',
        'identificacion',
        'comprobante_domicilio',
        'examen_psicofisico_integral',
        'id_user'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
