<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'event_id',
        'participant_id',
    ];

    /**
     * Relación con el participante
     */
    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    /**
     * Relación con el evento
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}