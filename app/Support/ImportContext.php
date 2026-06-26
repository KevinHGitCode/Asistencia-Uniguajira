<?php

namespace App\Support;

use App\Models\User;

/**
 * Datos del contexto de importación que el parser necesita y que normalmente
 * vendrían del request/auth. Se captura en el controlador (donde sí hay request)
 * y se pasa al parser, de modo que el parseo pueda correr también dentro de un
 * job en cola (sin request ni sesión).
 */
class ImportContext
{
    public function __construct(
        public readonly ?int $userId,
        public readonly bool $isSuperadmin,
        public readonly ?int $userCampusId,
        public readonly ?int $defaultCampusId,
    ) {}

    public static function fromUser(User $user, ?int $defaultCampusId): self
    {
        return new self(
            userId: $user->id,
            isSuperadmin: $user->isSuperadmin(),
            userCampusId: $user->campus_id !== null ? (int) $user->campus_id : null,
            defaultCampusId: $defaultCampusId,
        );
    }
}
