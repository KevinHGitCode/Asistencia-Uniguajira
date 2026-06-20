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
        'campus_id',
        'academic_program_id',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'participant_roles')
            ->wherePivot('is_active', true);
    }

    public function participantRoles()
    {
        return $this->hasMany(ParticipantRole::class);
    }
}
