<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Avatar extends Component
{
    use WithFileUploads;

    public User $user;
    public $size = 'h-12 w-12';
    public $textSize = 'text-lg';
    public $showUpload = false;
    public $photo;

    public function mount(User $user, $size = 'h-12 w-12', $textSize = 'text-lg', $showUpload = false)
    {
        $this->user = $user;
        $this->size = $size;
        $this->textSize = $textSize;
        $this->showUpload = $showUpload;
    }

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:2048', // 2MB Max
        ]);

        if ($this->photo) {
            // Eliminar avatar anterior si existe
            if ($this->user->avatar) {
                Storage::delete('public/' . $this->user->avatar);
            }

            // Guardar nuevo avatar
            $path = $this->photo->store('avatars', 'public');
            $this->user->update(['avatar' => $path]);

            $this->photo = null;
            $this->dispatch('avatar-updated');
        }
    }

    public function removeAvatar()
    {
        if ($this->user->avatar) {
            Storage::delete('public/' . $this->user->avatar);
            $this->user->update(['avatar' => null]);
            $this->dispatch('avatar-updated');
        }
    }

    public function render()
    {
        return view('livewire.user.avatar');
    }
}