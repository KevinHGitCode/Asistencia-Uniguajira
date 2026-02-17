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
        $user = Auth::user();
        $event = Event::findOrFail($id);

        // ✅ Si es admin, puede ver cualquier evento
        if ($user->role === 'admin') {
            $asistenciasCount = Attendance::where('event_id', $event->id)->count();
            return view('events.show', compact('event', 'asistenciasCount'));
        }

        // ✅ Si es creador del evento o pertenece a la misma dependencia
        if ($event->user_id === $user->id || $event->dependency_id === $user->dependency_id) {
            $asistenciasCount = Attendance::where('event_id', $event->id)->count();
            return view('events.show', compact('event', 'asistenciasCount'));
        }

        // 🚫 Si no tiene permisos
        abort(403, 'No tienes permiso para ver este evento.');
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

    public function getByDate($date)
    {
        $events = Event::whereDate('date', $date)->get();
        return response()->json($events);
    }

    public function descargarAsistencia($id)
    {
        $evento = Event::with(['asistencias.participant'])->findOrFail($id);
        // Función auxiliar
        function limitarTexto($texto, $limite = 25) {
            return mb_strlen($texto) > $limite
                ? mb_substr($texto, 0, $limite - 3) . '...'
                : $texto;
        }

        $pdf = new Fpdi();
        $path = public_path('formatos/LISTADO_DE_ASISTENCIA_GENERAL_REVISION_8.pdf');

        if (!file_exists($path)) {
            dd('Archivo no encontrado en:', $path);
        }

        // Cargar formato base
        $pageCount = $pdf->setSourceFile($path);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        // Crear primera página
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplIdx);
        $pdf->SetFont('Arial', 'B', 12);


        // Dependencia del evento
        $pdf->SetXY(78, 30.5);
        $pdf->Cell(0, 8,
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper($evento->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8')),
            0, 0, 'L'
        );

        // Título del evento
        $pdf->SetXY(44, 38.5); // coordenadas exactas según tu formato
        $pdf->Cell(0,8,
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper($evento->title ?? 'SIN TÍTULO', 'UTF-8')),
            0, 0, 'L'
        );

        // Fecha del evento
        $pdf->SetXY(224, 30.5);
        $pdf->Cell(0,8,
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', Carbon::parse($evento->date)->format('d   m    Y')),
            0, 0, 'L'
        );

        $pdf->SetFont('Arial', '', 12);

        $startY = 62.2;
        $rowHeight = 8.15;
        $maxRows = 16;
        $row = 0;

        foreach ($evento->asistencias as $i => $asistencia) {
            $p = $asistencia->participant;

            // Si se llena la hoja, crear una nueva con el formato
            if ($row >= $maxRows) {
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplIdx);
                $row = 0;

                $pdf->SetFont('Arial', 'B', 12);

                // Dependencia del evento
                $pdf->SetXY(78, 30.5);
                $pdf->Cell(0, 8,
                    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper($evento->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8')),
                    0, 0, 'L'
                );

                // Título del evento
                $pdf->SetXY(44, 38.5); // coordenadas exactas según tu formato
                $pdf->Cell(0,8,
                    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', mb_strtoupper($evento->title ?? 'SIN TÍTULO', 'UTF-8')),
                    0, 0, 'L'
                );

                // Fecha del evento
                $pdf->SetXY(224, 30.5);
                $pdf->Cell(0,8,
                    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', Carbon::parse($evento->date)->format('d   m    Y')),
                    0, 0, 'L'
                );

                $pdf->SetFont('Arial', '', 12);
            }


            $y = round($startY + ($row * $rowHeight), 2);


            // === Datos del asistente ===

            // Numero de registro
            $pdf->SetXY(19, $y);
            $pdf->Cell(12, 7.8, $i + 1, 0, 0, 'C');

            // Nombres y Apellidos
            $pdf->SetXY(30.2, $y);
            $pdf->Cell(61,7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto(trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')), 32)
                ),
                0, 0, 'L'
            );

            // Cargo o rol
            $pdf->SetXY(105.5, $y);
            $pdf->Cell(28,7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->role ?? '', 13)
                ),
                0, 0, 'L'
            );

            // Dependencia o programa
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(137.3, $y);
            $pdf->Cell(34,7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->program->name ?? '', 18)
                ),
                0, 0, 'L'
            );

            // Correo electrónico
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(180, $y);
            $pdf->Cell(34,7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->email ?? '', 30)
                ),
                0, 0, 'L'
            );

            //$pdf->SetFont('Arial', '', 12);
            // Hora de registro
            $pdf->SetXY(242.2, $y);
            $pdf->Cell(20, 7.8, Carbon::parse($asistencia->created_at)->format('h:i A'), 0, 0, 'C');

            $pdf->SetFont('Arial', '', 12);

            // // === Sexo ===
            // if (($p->gender ?? '') === 'F') { $pdf->SetXY(178, $y); $pdf->Write(0, 'X'); }
            // if (($p->gender ?? '') === 'M') { $pdf->SetXY(184, $y); $pdf->Write(0, 'X'); }

            // // === Grupo priorizado ===
            // $grupos = is_array($p->priority_group)
            //     ? $p->priority_group
            //     : explode(',', $p->priority_group ?? '');

            // foreach ($grupos as $g) {
            //     switch (trim($g)) {
            //         case 'E': $pdf->SetXY(192, $y); $pdf->Write(0, 'X'); break;
            //         case 'D': $pdf->SetXY(198, $y); $pdf->Write(0, 'X'); break;
            //         case 'V': $pdf->SetXY(204, $y); $pdf->Write(0, 'X'); break;
            //         case 'C': $pdf->SetXY(210, $y); $pdf->Write(0, 'X'); break;
            //         case 'H': $pdf->SetXY(216, $y); $pdf->Write(0, 'X'); break;
            //     }
            // }

            $row++;
        }

        // for ($x = 0; $x <= 290; $x += 10) {
        //     $pdf->SetXY($x, 5);
        //     $pdf->Write(0, "|$x");
        // }

        // for ($y = 0; $y <= 200; $y += 10) {
        //     $pdf->SetXY(5, $y);
        //     $pdf->Write(0, "-$y");
        // }


        $nombreEvento = str_replace(' ', '_', $evento->title);
        $fecha = Carbon::parse($evento->date)->format('Y-m-d');
        $nombreArchivo = "Asistencia_{$nombreEvento}_{$fecha}.pdf";

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
        ]);
    }



}
