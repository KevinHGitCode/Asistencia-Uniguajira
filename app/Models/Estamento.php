<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estamento extends Model
{
    protected $fillable = ['name'];

    /**
     * Participantes que pertenecen a este estamento.
     * La relación usa el campo 'role' (string) de participants como FK lógica.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'role', 'name');
    }
}
