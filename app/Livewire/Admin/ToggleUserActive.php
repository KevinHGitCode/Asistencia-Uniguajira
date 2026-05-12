<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\ActivityLogService;
use Livewire\Component;
use Flux\Flux as FluxAlias;

class ToggleUserActive extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function toggleActive()
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Solo administradores pueden cambiar el estado de usuarios.');

        $oldStatus = $this->user->is_active ? 'activo' : 'inactivo';
        $this->user->update(['is_active' => !$this->user->is_active]);

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