<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vinculacion extends Model
{
    protected $table = 'vinculaciones';

    protected $fillable = [
        'tipo_vinculacion',
    ];


    public function participantes()
    {
        return $this->hasMany(Participante::class, 'vinculacion_id');
    }
}
