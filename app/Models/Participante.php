<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    protected $table = 'participantes';

    protected $fillable = [
        'documento',
        'nombres',
        'apellidos',
        'email',
        'estamento_id',
        'programa_id',
        'vinculacion_id',
    ];

    public function estamento()
    {
        return $this->belongsTo(Estamento::class, 'estamento_id');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'programa_id');
    }

    public function vinculacion()
    {
        return $this->belongsTo(Vinculacion::class, 'vinculacion_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'participante_id');
    }

    public function eventos()
    {
        return $this->belongsToMany(Event::class, 'asistencias', 'participante_id', 'evento_id')
            ->withPivot('fecha_hora')
            ->withTimestamps();
    }
}
