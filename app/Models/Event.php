<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'date', // fecha del evento
        'start_time', // hora de inicio
        'end_time', // hora de finalización
        'user_id', // Agregar user_id al fillable
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Attendance::class, 'event_id');
    }

    public function participantes()
    {
        return $this->belongsToMany(Participant::class, 'attendances', 'event_id', 'participant_id')
            ->withTimestamps();
    }
}