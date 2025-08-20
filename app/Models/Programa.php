<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $table = 'programas';

    protected $fillable = [
        'nombre',
    ];


    public function participantes()
    {
        return $this->hasMany(Participante::class, 'programa_id');
    }
}
