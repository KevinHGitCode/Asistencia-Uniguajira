<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AttendancePdfService;
use App\Services\CampusScopeService;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(CampusScopeService $campusScope)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasAdminAccess()) {
            $myEvents = $campusScope->applyToQuery(
                Event::with(['dependency:id,name', 'area:id,name', 'user:id,name']),
                $user
            )
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('events.list', [
                'myEvents' => $myEvents,
                'dependencyEvents' => collect(),
                'dependenciesNames' => '',
            ]);
        }

        $user->loadMissing('dependencies');
        $dependencyIds = $user->dependencies
            ->where('campus_id', $user->campus_id)
            ->pluck('id');

        $myEvents = $campusScope->applyToQuery(
            Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
                ->where('user_id', $user->id),
            $user
        )
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $dependencyEvents = collect();

        if ($dependencyIds->isNotEmpty()) {
            $dependencyEvents = $campusScope->applyToQuery(
                Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
                    ->whereIn('dependency_id', $dependencyIds)
                    ->where('user_id', '!=', $user->id),
                $user
            )
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $dependenciesNames = $user->dependencies
            ->where('campus_id', $user->campus_id)
            ->pluck('name')
            ->join(' - ');

        return view('events.list', compact('myEvents', 'dependencyEvents', 'dependenciesNames'));
    }

    public function create()
    {
        return view('events.new');
    }

    public function store(Request $request, EventService $eventService)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
                'location' => 'required|string|max:255',
                'campus_id' => 'nullable|exists:campuses,id',
                'dependency_id' => 'required|exists:dependencies,id',
                'area_id' => 'nullable|exists:areas,id',
            ]);

            $event = $eventService->create($validated, Auth::user());

            return redirect()
                ->route('events.new')
                ->with('success', 'Evento creado exitosamente.')
                ->with('event_link', route('events.access', ['slug' => $event->link]));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating event: '.$e->getMessage());

            return back()->withInput()->withErrors(['error' => 'Hubo un error al crear el evento.']);
        }
    }

    public function show(string $id, CampusScopeService $campusScope)
    {
        /** @var User $user */
        $user = Auth::user();

        $event = Event::with(['dependency.formats', 'area', 'user'])->findOrFail($id);
        $asistenciasCount = Attendance::where('event_id', $event->id)->count();

        $perteneceDependencia = $user->dependencies()
            ->where('dependencies.id', $event->dependency_id)
            ->exists();

        $tienePermiso = $campusScope->canAccessResource($user, $event) && (
            $user->hasAdminAccess()
            || (int) $event->user_id === (int) $user->id
            || $perteneceDependencia
        );

        if (! $tienePermiso) {
            abort(403, 'No tienes permiso para ver este evento.');
        }

        return view('events.show', compact('event', 'asistenciasCount'));
    }

    public function access($slug)
    {
        $event = Event::where('link', $slug)->firstOrFail();

        return view('events.access', compact('event'));
    }

    public function destroy($id, CampusScopeService $campusScope)
    {
        $event = Event::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        if (! $this->canManagePrivateEvent($user, $event, $campusScope)) {
            abort(403);
        }

        if (! $event->is_deletable) {
            return back()->withErrors(['error' => 'No se puede eliminar un evento que ya pasó.']);
        }

        $eventTitle = $event->title;
        $event->delete();

        ActivityLogService::log('eliminar', 'eventos', "Eliminó el evento '{$eventTitle}'");

        return redirect()->route('events.list')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    public function end(Event $event, CampusScopeService $campusScope)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $this->canManagePrivateEvent($user, $event, $campusScope)) {
            abort(403);
        }

        if (! $event->isOpenForAttendance()) {
            return back()->with('error', 'Este evento ya ha finalizado.');
        }

        $event->update(['ended_at' => now()]);

        ActivityLogService::log('terminar_evento', 'eventos', "Terminó manualmente el evento '{$event->title}'", $event);

        return back()->with('success', 'El evento ha sido finalizado exitosamente.');
    }

    public function areas(Dependency $dependency, CampusScopeService $campusScope)
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $campusScope->canAccessResource($user, $dependency)) {
            abort(403);
        }

        if (! $user->hasAdminAccess()
            && ! $user->dependencies()->where('dependencies.id', $dependency->id)->exists()) {
            abort(403);
        }

        return $dependency->areas()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getByDate($date, CampusScopeService $campusScope)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
            ->whereDate('date', $date);

        $campusScope->applyToQuery($query, $user);

        if (! $user->hasAdminAccess()) {
            $user->loadMissing('dependencies');
            $dependencyIds = $user->dependencies->pluck('id')->all();

            $query->where(function ($eventQuery) use ($user, $dependencyIds) {
                $eventQuery->where('user_id', $user->id);

                if ($dependencyIds !== []) {
                    $eventQuery->orWhereIn('dependency_id', $dependencyIds);
                }
            });
        }

        $events = $query
            ->get()
            ->map(function ($event) {
                $event->dependency_name = $event->dependency?->name;
                $event->area_name = $event->area?->name;
                $event->creator_name = $event->user?->name;

                return $event;
            });

        return response()->json($events);
    }

    public function descargarAsistencia(AttendancePdfService $pdfService, CampusScopeService $campusScope, $id, $formatSlug = null)
    {
        $evento = Event::with([
            'campus',
            'asistencias.participant.activeRoles.type',
            'asistencias.participant.activeRoles.program',
            'asistencias.participant.activeRoles.dependency',
            'asistencias.participant.activeRoles.affiliation',
            'asistencias.detail.participantRole.type',
            'asistencias.detail.participantRole.program',
            'asistencias.detail.participantRole.organization',
            'dependency.campus',
            'dependency.formats',
            'area',
            'user',
        ])->findOrFail($id);

        /** @var User $user */
        $user = Auth::user();

        $perteneceDependencia = $evento->dependency_id
            && $user->dependencies()
                ->where('dependencies.id', $evento->dependency_id)
                ->exists();

        if (! $this->canAccessEventPdf($user, $evento, $campusScope)
            || (! $user->hasAdminAccess()
                && (int) $evento->user_id !== (int) $user->id
                && ! $perteneceDependencia)) {
            abort(403, 'No tienes permiso para descargar la asistencia de este evento.');
        }

        $formats = $evento->dependency->formats ?? collect();

        if ($formats->isEmpty()) {
            $formatSlug = 'general';
        }

        if (! $formatSlug) {
            $formatSlug = 'general';
        }

        if ($formats->isNotEmpty() && ! $formats->contains('slug', $formatSlug) && $formatSlug !== 'general') {
            abort(403, 'Esta dependencia no tiene acceso a este formato.');
        }

        try {
            $pdfContent = $pdfService->generatePdf($evento, $formatSlug);
        } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            return back()->with('error',
                'La plantilla PDF del formato "'.$formatSlug.'" usa una versión de PDF no soportada (1.5 o superior). '
                .'Debe re-subir la plantilla en versión PDF 1.4 o inferior desde la configuración de formatos. Comuniquese con el administrador.'
            );
        }

        ActivityLogService::log('exportar', 'eventos', "Descargó PDF de asistencias del evento '{$evento->title}'", $evento);

        $nombreArchivo = 'Asistencia_'.str_replace(' ', '_', $evento->title).'_'.\Carbon\Carbon::parse($evento->date)->format('Y-m-d').'.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }

    private function canAccessEventPdf(User $user, Event $event, CampusScopeService $campusScope): bool
    {
        $campusId = $event->campus_id ?? $event->dependency?->campus_id;

        return $campusScope->canAccessCampus($user, $campusId !== null ? (int) $campusId : null);
    }

    private function canManagePrivateEvent(User $user, Event $event, CampusScopeService $campusScope): bool
    {
        if (! $campusScope->canAccessResource($user, $event)) {
            return false;
        }

        if ($user->hasAdminAccess()) {
            return true;
        }

        return $event->user_id !== null && (int) $event->user_id === (int) $user->id;
    }
}
