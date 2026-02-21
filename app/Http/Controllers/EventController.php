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

        return view('events.list', compact('myEvents', 'dependencyEvents'));
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
    public function store(Request $request)
    {
        try {

            /** @var User $user */
            $user = Auth::user();


            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
                'location' => 'required|string|max:255',
                'dependency_id' => 'nullable|exists:dependencies,id',
                'area_id' => 'nullable|exists:areas,id',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Seguridad dependencia
            |--------------------------------------------------------------------------
            */

            if ($user->role === 'admin') {

                // Admin puede dejarla null o elegir cualquiera
                $validated['dependency_id'] = $request->input('dependency_id');

            } else {

                // Usuario normal: verificar que la dependencia enviada
                // realmente le pertenezca
                $allowedDependencies = $user->dependencies->pluck('id')->toArray();

                if (
                    !$request->filled('dependency_id') ||
                    !in_array($request->input('dependency_id'), $allowedDependencies)
                ) {
                    return back()
                        ->withInput()
                        ->withErrors(['dependency_id' => 'Dependencia no válida.']);
                }

                $validated['dependency_id'] = $request->input('dependency_id');
            }

            /*
            |--------------------------------------------------------------------------
            | Seguridad área (si existe)
            |--------------------------------------------------------------------------
            */

            if ($request->filled('area_id')) {

                $area = Area::where('id', $request->area_id)
                    ->where('dependency_id', $validated['dependency_id'])
                    ->first();

                if (!$area) {
                    return back()
                        ->withInput()
                        ->withErrors(['area_id' => 'Área no válida para la dependencia seleccionada.']);
                }

                $validated['area_id'] = $area->id;

            } else {
                $validated['area_id'] = null;
            }

            /*
            |--------------------------------------------------------------------------
            | Datos adicionales
            |--------------------------------------------------------------------------
            */

            $validated['user_id'] = $user->id;

            $slug = str_replace(' ', '-', strtolower($validated['title']))
                . '-' . date('Ymd', strtotime($validated['date']))
                . '-' . uniqid();

            $validated['link'] = $slug;

            Event::create($validated);

            return redirect()
                ->route('events.new')
                ->with('success', 'Evento creado exitosamente.')
                ->with('event_link', route('events.access', ['slug' => $validated['link']]));

        } catch (\Exception $e) {

            Log::error('Error creating event: ' . $e->getMessage());

            $errorMsg = 'Hubo un error al crear el evento.';

            if (app()->environment() !== 'production') {
                $errorMsg .= ' Detalles: ' . $e->getMessage();
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $errorMsg]);
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
    public function destroy(string $id)
    {
        //
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
