<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Campos que permitimos llenar masivamente
    protected $fillable = [
        'title', 
        'description', 
        'status', 
        'priority', 
        'due_date', 
        'user_id',
        'started_at',
        'employee_notes',
        'evidence_path'
    ];

    // Relación: Una tarea pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}