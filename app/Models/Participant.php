<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'document',
        'student_code',
        'first_name',
        'last_name',
        'email',
    ];

    public function activeRoles()
    {
        return $this->hasMany(ParticipantRole::class)->where('is_active', true);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'attendances')
            ->withTimestamps();
    }
}
