<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pending extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'status',
        'date_to_complete',
        'is_urgent',
        'id_created_by',
        'id_assigned_to'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'id_created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'id_assigned_to');
    }
}
