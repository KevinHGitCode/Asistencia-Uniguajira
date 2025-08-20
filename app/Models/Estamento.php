<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estamento extends Model
{
    protected $table = 'estamentos';

    protected $fillable = [
        'tipo_estamento',
    ];

    public function participantes()
    {
        return $this->hasMany(Participante::class, 'estamento_id');
    }
}
