<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Event;
use App\Models\Dependency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use Carbon\Carbon;
use setasign\Fpdi\Tfpdf\Fpdi;
use App\Services\AttendancePdfService;
use App\Services\EventService;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Una sola consulta de dependencias reutilizada en todo el método
        $user->loadMissing('dependencies');
        $dependencyIds = $user->dependencies->pluck('id');

        // Eventos propios (con relaciones restringidas a columnas necesarias)
        $myEvents = Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Eventos de las dependencias del usuario (excluyendo los propios)
        $dependencyEvents = collect();

        if ($dependencyIds->isNotEmpty()) {
            $dependencyEvents = Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
                ->whereIn('dependency_id', $dependencyIds)
                ->where('user_id', '!=', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $dependenciesNames = $user->dependencies->pluck('name')->join(' - ');

        return view('events.list', compact('myEvents', 'dependencyEvents', 'dependenciesNames'));
    }


    /**
     * Show the form for creating a new resource.
     * Los datos (dependencias, áreas) los carga el propio componente Livewire.
     */
    public function create()
    {
        return view('events.new');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, EventService $eventService)
    {
        try {
            $validated = $request->validate([
                'title'         => 'required|string|max:255',
                'description'   => 'nullable|string',
                'date'          => 'required|date',
                'start_time'    => 'nullable|date_format:H:i',
                'end_time'      => 'nullable|date_format:H:i|after_or_equal:start_time',
                'location'      => 'required|string|max:255',
                'dependency_id' => 'nullable|exists:dependencies,id',
                'area_id'       => 'nullable|exists:areas,id',
            ]);

            $event = $eventService->create($validated, Auth::user());

            return redirect()
                ->route('events.new')
                ->with('success', 'Evento creado exitosamente.')
                ->with('event_link', route('events.access', ['slug' => $event->link]));

        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Hubo un error al crear el evento.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $event = Event::with(['dependency.formats', 'area', 'user'])
            ->findOrFail($id);

        $asistenciasCount = Attendance::where('event_id', $event->id)->count();

        $perteneceDependencia = $user->dependencies()
            ->where('dependencies.id', $event->dependency_id)
            ->exists();

        $tienePermiso =
            $user->role === 'admin' ||
            $event->user_id === $user->id ||
            $perteneceDependencia;

        if (!$tienePermiso) {
            abort(403, 'No tienes permiso para ver este evento.');
        }

        return view('events.show', compact('event', 'asistenciasCount'));
    }



    public function access($slug){
      $event = Event::where('link', $slug)->firstOrFail();
       return view('events.access', compact('event'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $user = Auth::user();

        $isOwner = $event->user_id !== null && (int) $event->user_id === (int) $user->id;

        if ($user->role !== 'admin' && !$isOwner) {
            abort(403);
        }

        if (!$event->is_deletable) {
            return back()->withErrors(['error' => 'No se puede eliminar un evento que ya pasó.']);
        }

        $event->delete();

        return redirect()->route('events.list')
            ->with('success', 'Evento eliminado exitosamente.');
    }

    public function areas(Dependency $dependency)
    {
        return $dependency->areas()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }


    public function getByDate($date)
    {
        $events = Event::with(['dependency:id,name', 'area:id,name', 'user:id,name'])
            ->whereDate('date', $date)
            ->get()
            ->map(function ($e) {
                $e->dependency_name = $e->dependency?->name;
                $e->area_name       = $e->area?->name;
                $e->creator_name    = $e->user?->name;
                return $e;
            });

        return response()->json($events);
    }

    public function descargarAsistencia(AttendancePdfService $pdfService, $id, $formatSlug = null)
    {
        $evento = Event::with([
            'asistencias.participant.activeRoles.type',
            'asistencias.participant.activeRoles.program',
            'asistencias.participant.activeRoles.dependency',
            'asistencias.participant.activeRoles.affiliation',
            'asistencias.detail.participantRole.type',
            'asistencias.detail.participantRole.program',
            'dependency.formats',
            'area',
            'user',
        ])->findOrFail($id);

        // ── Autorización: admin, dueño o miembro de la dependencia ──
        /** @var User $user */
        $user = Auth::user();

        $perteneceDependencia = $evento->dependency_id
            && $user->dependencies()
                ->where('dependencies.id', $evento->dependency_id)
                ->exists();

        if ($user->role !== 'admin'
            && (int) $evento->user_id !== (int) $user->id
            && ! $perteneceDependencia) {
            abort(403, 'No tienes permiso para descargar la asistencia de este evento.');
        }

        $formats = $evento->dependency->formats ?? collect();

        // Si no tiene formatos asignados, usa general
        if ($formats->isEmpty()) {
            $formatSlug = 'general';
        }

        // Si no se pasó slug, usa general
        if (!$formatSlug) {
            $formatSlug = 'general';
        }

        // Validar acceso solo si tiene formatos asignados
        if ($formats->isNotEmpty() && !$formats->contains('slug', $formatSlug) && $formatSlug !== 'general') {
            abort(403, 'Esta dependencia no tiene acceso a este formato.');
        }

        try {
            $pdfContent = $pdfService->generatePdf($evento, $formatSlug);
        } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            return back()->with('error',
                'La plantilla PDF del formato "' . $formatSlug . '" usa una versión de PDF no soportada (1.5 o superior). '
                . 'Debe re-subir la plantilla en versión PDF 1.4 o inferior desde la configuración de formatos. Comuniquese con el administrador.'
            );
        }

        $nombreArchivo = "Asistencia_".str_replace(' ', '_', $evento->title)."_".\Carbon\Carbon::parse($evento->date)->format('Y-m-d').".pdf";

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }
}
