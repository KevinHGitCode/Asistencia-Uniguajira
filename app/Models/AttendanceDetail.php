<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    protected $fillable = [
        'attendance_id',
        'gender',
        'telefono',
        'municipio',
        'barrio',
        'direccion',
        'priority_group',
        'program_id',
        'participant_type_id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Programa con el que el participante asistió a este evento.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Tipo de estamento con el que el participante se registró en este evento.
     */
    public function participantType()
    {
        return $this->belongsTo(ParticipantType::class, 'participant_type_id');
    }
}
