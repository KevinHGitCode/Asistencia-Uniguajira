<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Event;
use App\Models\User;
use Illuminate\Validation\ValidationException;

use App\Mail\EventCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventService
{
    public function create(array $data, User $user): Event
    {
        // Seguridad dependencia
        if ($user->role !== 'admin' && !empty($data['dependency_id'])) {
            $allowed = $user->dependencies->pluck('id')->toArray();
            if (!in_array($data['dependency_id'], $allowed)) {
                throw ValidationException::withMessages([
                    'dependency_id' => 'Dependencia no válida.',
                ]);
            }
        }

        // Seguridad área
        $areaId = null;
        if (!empty($data['area_id'])) {
            $area = Area::where('id', $data['area_id'])
                ->where('dependency_id', $data['dependency_id'])
                ->first();

            if (!$area) {
                throw ValidationException::withMessages([
                    'area_id' => 'Área no válida para la dependencia seleccionada.',
                ]);
            }
            $areaId = $area->id;
        }

        // Generar slug
        $slug = str_replace(' ', '-', strtolower($data['title']))
            . '-' . date('Ymd', strtotime($data['date']))
            . '-' . uniqid();

        $event = Event::create([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'location'      => $data['location'] ?? null,
            'dependency_id' => $data['dependency_id'] ?: null,
            'area_id'       => $areaId,
            'date'          => $data['date'],
            'start_time'    => $data['start_time'] ?? null,
            'end_time'      => $data['end_time'] ?? null,
            'user_id'       => $user->id,
            'link'          => $slug,
        ]);

        if ($user->email) {
            try {
                $event->load(['dependency', 'area', 'user']);
                Mail::to($user->email)->send(new EventCreatedMail($event));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de evento creado: ' . $e->getMessage());
            }
        }

        return $event;
    }
}