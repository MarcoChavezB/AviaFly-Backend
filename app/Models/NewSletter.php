<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewSletter extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'file',
        'direct_to',
        'start_at',
        'expired_at',
        'is_active',
        'created_by'
    ];

    public function created_by(){
        return $this->belongsTo(Employee::class, 'created_by');
    }

 }
