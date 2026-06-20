<?php

namespace App\Services;

use App\Mail\EventCreatedMail;
use App\Models\Area;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function create(array $data, User $user): Event
    {
        $campusId = app(CampusScopeService::class)->activeCampusId($user);
        $dependency = null;

        if ($campusId === null && ! $user->isSuperadmin()) {
            throw ValidationException::withMessages([
                'campus_id' => 'Tu usuario no tiene una sede asignada.',
            ]);
        }

        if (! empty($data['dependency_id'])) {
            $dependency = Dependency::find($data['dependency_id']);

            if (! $dependency) {
                throw ValidationException::withMessages([
                    'dependency_id' => 'Dependencia no válida.',
                ]);
            }

            if ($campusId !== null && (int) $dependency->campus_id !== (int) $campusId) {
                throw ValidationException::withMessages([
                    'dependency_id' => 'La dependencia no pertenece a tu sede.',
                ]);
            }

            if ($campusId === null && $user->isSuperadmin()) {
                $campusId = $dependency->campus_id !== null ? (int) $dependency->campus_id : null;
            }

            if (! $user->hasAdminAccess()) {
                $allowed = $user->dependencies->pluck('id')->map(fn ($id) => (int) $id)->all();

                if (! in_array((int) $data['dependency_id'], $allowed, true)) {
                    throw ValidationException::withMessages([
                        'dependency_id' => 'Dependencia no válida.',
                    ]);
                }
            }
        }

        if ($campusId === null) {
            throw ValidationException::withMessages([
                'dependency_id' => 'Selecciona una sede activa o una dependencia para crear el evento.',
            ]);
        }

        $areaId = null;
        if (! empty($data['area_id'])) {
            $area = Area::where('id', $data['area_id'])
                ->where('dependency_id', $data['dependency_id'])
                ->where('campus_id', $campusId)
                ->first();

            if (! $area) {
                throw ValidationException::withMessages([
                    'area_id' => 'Área no válida para la dependencia seleccionada.',
                ]);
            }

            $areaId = $area->id;
        }

        if ($dependency && $dependency->campus_id === null) {
            throw ValidationException::withMessages([
                'dependency_id' => 'La dependencia seleccionada no tiene sede asignada.',
            ]);
        }

        $base = str_replace(' ', '-', strtolower($data['title']))
            .'-'.date('Ymd', strtotime($data['date']));

        $maxAttempts = 5;
        $slug = null;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $candidate = $base.'-'.bin2hex(random_bytes(6));
            if (! Event::where('link', $candidate)->exists()) {
                $slug = $candidate;
                break;
            }
        }

        if ($slug === null) {
            throw new \RuntimeException('No se pudo generar un slug único para el evento.');
        }

        $event = Event::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
            'dependency_id' => $data['dependency_id'] ?: null,
            'area_id' => $areaId,
            'date' => $data['date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'user_id' => $user->id,
            'campus_id' => $campusId,
            'link' => $slug,
        ]);

        ActivityLogService::log('crear', 'eventos', "Creó el evento '{$event->title}'", $event, userId: $user->id);

        if ($user->email) {
            try {
                $event->load(['dependency', 'area', 'user']);
                Mail::to($user->email)->send(new EventCreatedMail($event));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de evento creado: '.$e->getMessage());
            }
        }

        return $event;
    }
}
