<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{

    protected $fillable = [
        'name',
        'campus',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class, 'program_id');
    }
}
