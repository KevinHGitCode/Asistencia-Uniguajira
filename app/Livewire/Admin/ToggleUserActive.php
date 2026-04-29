<?php

namespace App\Livewire\Admin;

use App\Models\User;
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

        $this->user->update(['is_active' => !$this->user->is_active]);

        $status = $this->user->is_active ? 'activado' : 'desactivado';

        return redirect()->route('users.information', ['id' => $this->user->id])
            ->with('success', "Usuario {$status} correctamente.");
    }

    public function render()
    {
        return view('livewire.admin.toggle-user-active');
    }
}