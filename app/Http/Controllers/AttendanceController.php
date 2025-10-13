<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Registrar asistencia a un evento
     */
    public function store(Request $request, $slug)
    {
        try {
            // Validar el número de documento
            $validated = $request->validate([
                'identification' => 'required|string|max:20',
            ], [
                'identification.required' => 'El número de identificación es obligatorio.',
                'identification.max' => 'El número de identificación no puede tener más de 20 caracteres.',
            ]);

            // Buscar el evento por el slug
            $event = Event::where('link', $slug)->firstOrFail();

            // Buscar el participante por número de documento
            $participant = Participant::where('document', $validated['identification'])->first();

            // Si el participante no existe, retornar error
            if (!$participant) {
                return back()->withErrors([
                    'identification' => 'No se encontró un participante registrado con este número de identificación.'
                ])->withInput();
            }

            // Verificar si ya registró asistencia a este evento
            $existingAttendance = Attendance::where('event_id', $event->id)
                ->where('participant_id', $participant->id)
                ->first();

            if ($existingAttendance) {
                return back()->withErrors([
                    'identification' => 'Ya has registrado tu asistencia a este evento.'
                ])->withInput();
            }

            // Registrar la asistencia
            $attendance = Attendance::create([
                'event_id' => $event->id,
                'participant_id' => $participant->id,
            ]);

            // Redirigir a la página de confirmación con información del participante
            return redirect()->route('attendance.confirmation', [
                'slug' => $event->link,
                'attendanceId' => $attendance->id
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->withErrors([
                'error' => 'El evento no existe o ha sido eliminado.'
            ])->withInput();
        } catch (\Exception $e) {
            Log::error('Error al registrar asistencia: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Hubo un error al registrar la asistencia. Por favor, inténtalo de nuevo.'
            ])->withInput();
        }
    }

    /**
     * Mostrar confirmación de asistencia registrada
     */
    public function confirmation($slug, $attendanceId)
    {
        try {
            // Buscar el evento
            $event = Event::where('link', $slug)->firstOrFail();

            // Buscar la asistencia con el participante
            $attendance = Attendance::with('participant.program')
                ->where('id', $attendanceId)
                ->where('event_id', $event->id)
                ->firstOrFail();

            $participant = $attendance->participant;

            // Contar total de asistencias del participante
            $totalAttendances = Attendance::where('participant_id', $participant->id)->count();

            return view('events.confirmation', compact('event', 'participant', 'attendance', 'totalAttendances'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('events.access', $slug)
                ->withErrors(['error' => 'No se encontró el registro de asistencia.']);
        } catch (\Exception $e) {
            Log::error('Error al mostrar confirmación: ' . $e->getMessage());
            return redirect()->route('events.access', $slug)
                ->withErrors(['error' => 'Hubo un error al cargar la información.']);
        }
    }
}