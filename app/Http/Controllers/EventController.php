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
        
        // Eventos propios (con relaciones)
        $myEvents = Event::with(['dependency', 'area', 'user'])
            ->where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Eventos de las dependencias del usuario (excluyendo los propios)
        $dependencyEvents = collect();

        if ($user->dependencies()->exists()) {

            $dependencyIds = $user->dependencies()->pluck('dependencies.id');

            $dependencyEvents = Event::with(['dependency', 'area', 'user'])
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
     */
    public function create()
    {
        $user = request()->user();

        if ($user->role === 'admin') {
            $dependencies = Dependency::orderBy('name')->get();
        } else {
            $dependencies = $user->dependencies()
                ->orderBy('name')
                ->get();
        }

        $selectedDependency = null;
        $areas = collect();

        if ($user->role !== 'admin' && $dependencies->count() === 1) {
            $selectedDependency = $dependencies->first()->id;

            $areas = Area::where('dependency_id', $selectedDependency)
                ->orderBy('name')
                ->get();
        }

        return view('events.new', compact(
            'dependencies',
            'selectedDependency',
            'areas'
        ));
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

        $event = Event::with(['dependency', 'area', 'user'])
            ->findOrFail($id);

        $asistenciasCount = Attendance::where('event_id', $event->id)->count();

        // ✔ Verificar si pertenece a la dependencia del evento
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



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
        $events = Event::whereDate('date', $date)->get();
        return response()->json($events);
    }


    public function descargarAsistencia($id, AttendancePdfService $pdfService)
    {
        $evento = Event::with('asistencias.participant.program', 'dependency', 'area')->findOrFail($id);
        $pdfContent = $pdfService->generatePdf($evento); // ya es string

        $nombreArchivo = "Asistencia_".str_replace(' ', '_', $evento->title)."_".\Carbon\Carbon::parse($evento->date)->format('Y-m-d').".pdf";

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }
}
