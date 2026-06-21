<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Validation\ValidationException;

class ParticipantRole extends Model
{
    protected $fillable = [
        'participant_id',
        'participant_type_id',
        'program_id',
        'dependency_id',
        'affiliation_id',
        'organization_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ParticipantRole $role): void {
            if (! $role->is_active || ! $role->program_id) {
                return;
            }

            $academicProgramId = Program::query()
                ->whereKey($role->program_id)
                ->value('academic_program_id');

            if (! $academicProgramId || ! app(\App\Services\ParticipantAcademicProgramService::class)
                ->hasActiveRoleForAcademicProgram((int) $role->participant_id, (int) $academicProgramId)) {
                return;
            }

            $academicProgramName = AcademicProgram::query()->find($academicProgramId)?->name ?? 'seleccionado';

            throw ValidationException::withMessages([
                'program_id' => "El participante ya tiene un rol activo para el programa académico \"{$academicProgramName}\".",
            ]);
        });
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ParticipantType::class, 'participant_type_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicProgram(): HasOneThrough
    {
        return $this->hasOneThrough(
            AcademicProgram::class,
            Program::class,
            'id',
            'id',
            'program_id',
            'academic_program_id'
        );
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Dependency::class);
    }

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
