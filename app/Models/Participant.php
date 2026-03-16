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
        'gender',
        'priority_group',
    ];

    /**
     * Accessor para compatibilidad con vistas legacy que usan ->affiliation (singular).
     * Devuelve la primera afiliación del pivot.
     */
    public function getAffiliationAttribute(): ?Affiliation
    {
        return $this->affiliations->first();
    }

    /**
     * Accessor para compatibilidad con vistas legacy que usan ->program (singular).
     * Devuelve el primer programa del pivot.
     */
    public function getProgramAttribute(): ?Program
    {
        return $this->programs->first();
    }

    // son varios
    public function affiliations()
    {
        return $this->belongsToMany(Affiliation::class, 'affiliation_participant')
            ->withTimestamps();
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
