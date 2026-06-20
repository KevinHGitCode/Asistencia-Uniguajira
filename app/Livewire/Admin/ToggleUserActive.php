<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\ActivityLogService;
use Livewire\Component;

class ToggleUserActive extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function toggleActive()
    {
        $authUser = auth()->user();

        abort_unless($authUser?->hasAdminAccess(), 403, 'Solo administradores pueden cambiar el estado de usuarios.');
        abort_if($authUser->isAdmin() && (int) $this->user->campus_id !== (int) $authUser->campus_id, 403, 'No puedes cambiar usuarios de otra sede.');

        $oldStatus = $this->user->is_active ? 'activo' : 'inactivo';
        $this->user->update(['is_active' => ! $this->user->is_active]);

        $status = $this->user->is_active ? 'activado' : 'desactivado';
        $newStatus = $this->user->is_active ? 'activo' : 'inactivo';

        ActivityLogService::log('editar', 'usuarios', "Cambió estado del usuario '{$this->user->name}' a {$status}", $this->user, [
            'is_active' => ['old' => $oldStatus, 'new' => $newStatus],
        ]);

        return redirect()->route('users.information', ['id' => $this->user->id])
            ->with('success', "Usuario {$status} correctamente.");
    }

    public function render()
    {
        return view('livewire.admin.toggle-user-active');
    }
}
