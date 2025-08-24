<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{

    protected $fillable = [
        'document',
        'first_name',
        'last_name',
        'email',
        'role_id',
        'program_id',
        'affiliation_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'participant_id');
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'attendances', 'participant_id', 'event_id')
            ->withTimestamps();
    }
}
