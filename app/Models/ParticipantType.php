<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantType extends Model
{
    protected $fillable = ['name'];

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'participant_type_participant')
            ->withTimestamps();
    }
}
