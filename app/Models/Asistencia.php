<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    public function participante()
    {
        return $this->belongsTo(Participante::class, 'participante_id');
    }

    public function evento()
    {
        return $this->belongsTo(Event::class, 'evento_id');
    }
}
