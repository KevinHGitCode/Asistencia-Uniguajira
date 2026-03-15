<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDetail extends Model
{
    protected $fillable = [
        'attendance_id',
        'sexo',
        'telefono',
        'address_id',
        'grupo_priorizado',
        'program_id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Programa con el que el participante asistió a este evento.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
