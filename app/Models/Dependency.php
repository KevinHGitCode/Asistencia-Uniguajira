<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dependency extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'dependency_user')
                    ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function formats()
    {
        return $this->belongsToMany(Format::class, 'dependency_format');
    }

    public function participantRoles()
    {
        return $this->hasMany(ParticipantRole::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'participant_roles')
            ->wherePivot('is_active', true);
    }
}