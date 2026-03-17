<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class About extends Component
{
    public string $version = '0.0.0';

    public function mount(): void
    {
        $this->version = $this->readPackageVersion();
    }

    private function readPackageVersion(): string
    {
        try {
            $path = base_path('package.json');
            if (! is_file($path)) {
                return $this->version;
            }

            $data = json_decode((string) file_get_contents($path), true);
            $version = is_array($data) ? ($data['version'] ?? null) : null;

            return is_string($version) && $version !== '' ? $version : $this->version;
        } catch (\Throwable $e) {
            return $this->version;
        }
    }
}
