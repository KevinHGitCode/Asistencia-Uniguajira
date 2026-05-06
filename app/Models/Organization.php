<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
    ];

    public function participantRoles()
    {
        return $this->hasMany(ParticipantRole::class);
    }
}
