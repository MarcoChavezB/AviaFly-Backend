<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'file',
        'directed_to_group',
        'id_directed_to_person',
        'id_directed_to_base',
    ];

    public function person(){
        return $this->belongsTo(User::class, 'id_directed_to_person');
    }

    public function base(){
        return $this->belongsTo(Base::class, 'id_directed_to_base');   
    }
}
