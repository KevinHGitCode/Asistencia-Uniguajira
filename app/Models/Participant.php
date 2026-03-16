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
        'role',
        'affiliation_id',
        'gender',
        'priority_group',
    ];

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }

    /**
     * Un participante puede pertenecer a varios programas académicos.
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'participant_program')
            ->withTimestamps();
    }

    /**
     * Un participante puede tener varios tipos/estamentos.
     */
    public function types()
    {
        return $this->belongsToMany(ParticipantType::class, 'participant_type_participant')
            ->withTimestamps();
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
