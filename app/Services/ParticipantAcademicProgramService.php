<?php

namespace App\Services;

use App\Models\ParticipantRole;

class ParticipantAcademicProgramService
{
    public function academicProgramIdForProgram(?int $programId): ?int
    {
        if (! $programId) {
            return null;
        }

        return \App\Models\Program::query()
            ->whereKey($programId)
            ->value('academic_program_id');
    }

    public function hasActiveRoleForAcademicProgram(int $participantId, int $academicProgramId, ?int $ignoreRoleId = null): bool
    {
        return ParticipantRole::query()
            ->where('participant_id', $participantId)
            ->where('is_active', true)
            ->when($ignoreRoleId, fn ($query) => $query->where('id', '<>', $ignoreRoleId))
            ->whereHas('program', fn ($query) => $query->where('academic_program_id', $academicProgramId))
            ->exists();
    }

    public function duplicateMessage(string $academicProgramName): string
    {
        return "El participante ya tiene un rol activo para el programa académico \"{$academicProgramName}\" en otra sede.";
    }
}
