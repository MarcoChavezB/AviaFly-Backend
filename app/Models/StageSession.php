<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StageSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_stage',
        'id_session',
    ];

    function stage() {
        return $this->belongsTo(Stage::class, 'id_stage');
    }

    function session() {
        return $this->belongsTo(Session::class, 'id_session');
    }
}
