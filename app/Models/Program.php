<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program_type',
        'campus',
    ];

    /**
     * Un programa puede tener muchos participantes (relación N:M).
     */
    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'participant_program')
            ->withTimestamps();
    }
}
