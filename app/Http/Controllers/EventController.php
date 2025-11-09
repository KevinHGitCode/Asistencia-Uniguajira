<?php

namespace App\Http\Controllers;

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
        $user = Auth::user();
    
    // Obtener solo los eventos del usuario autenticado
    // Ordenados por fecha más reciente primero
    $myEvents = Event::where('user_id', $user->id)
        ->orderBy('date', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();

    // Obtener eventos de la dependencia del usuario (excluyendo los propios)
    $dependencyEvents = collect();
    
    if ($user->dependency_id) {
        $dependencyEvents = Event::where('dependency_id', $user->dependency_id)
            ->where('user_id', '!=', $user->id) // Excluir eventos propios
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Pasar los eventos a la vista
    return view('events.list', compact('myEvents', 'dependencyEvents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dependencies = Dependency::orderBy('name')->get();
        return view('events.new', compact('dependencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after_or_equal:start_time',
                'location' => 'required|string|max:255',
                'dependency_id' => 'nullable|exists:dependencies,id',
            ]);

            $validated['user_id'] = Auth::id();

            $user = Auth::user();
            if ($user->role === 'admin') {
                // Si es admin, usar la dependencia seleccionada en el formulario
                // Si no seleccionó ninguna, dejar null
                $validated['dependency_id'] = $request->input('dependency_id');
            } else {
                // Si es usuario normal, usar su dependencia
                $validated['dependency_id'] = $user->dependency_id;
            }
            // Generar un link único para el evento
            $slug = str_replace(' ', '-', strtolower($validated['title'])) . '-' . date('Ymd', strtotime($validated['date'])) . '-' . uniqid();
            $validated['link'] = $slug;

            Event::create($validated);

            // Redirigir para evitar reenvío del formulario
            return redirect()->route('events.new')->with('success', 'Evento creado exitosamente.')
            ->with('event_link', route('events.access', ['slug' => $validated['link']]));
        } catch (\Exception $e) {
            // Log del error para debugging
            Log::error('Error creating event: ' . $e->getMessage());

            $errorMsg = 'Hubo un error al crear el evento. Por favor, inténtalo de nuevo.';
            if (app()->environment() !== 'production') {
                $errorMsg .= ' Detalles: ' . $e->getMessage();
            }
            // Mantener los datos del formulario con withInput()
            return back()->withInput()->withErrors(['error' => $errorMsg]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    $user = Auth::user();
    
    // Permitir ver el evento si:
    // 1. El usuario es el creador del evento
    // 2. El evento pertenece a la misma dependencia del usuario
    $event = Event::where('id', $id)
        ->where(function($query) use ($user) {
            $query->where('user_id', $user->id);
            
            // Si el usuario tiene dependencia, también puede ver eventos de su dependencia
            if ($user->dependency_id) {
                $query->orWhere('dependency_id', $user->dependency_id);
            }
        })
        ->firstOrFail();

    $asistenciasCount = Attendance::where('event_id', $event->id)->count();

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
        // Título del evento
        $pdf->SetXY(44, 39); // coordenadas exactas según tu formato
        $pdf->Cell(0,8,
            iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($evento->title ?? 'SIN TÍTULO')),
            0, 0, 'L'
        );

        // Fecha del evento
        $pdf->SetXY(224, 31);
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
                // Título del evento
                $pdf->SetXY(44, 39); // coordenadas exactas según tu formato
                $pdf->Cell(0,8,
                    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', strtoupper($evento->title ?? 'SIN TÍTULO')),
                    0, 0, 'L'
                );

                // Fecha del evento
                $pdf->SetXY(224, 31);
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
