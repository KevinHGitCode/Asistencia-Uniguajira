<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        // Obtener solo los eventos del usuario autenticado
        // Ordenados por fecha más reciente primero
        $events = Event::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Pasar los eventos a la vista
        return view('events.list', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.new');
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
            ]);

            $validated['user_id'] = Auth::id();

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
        $event = Event::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

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
        $path = public_path('formatos/Formato_Asistencia.pdf');

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
        $pdf->SetFont('Arial', '', 8);

        $startY = 82;
        $rowHeight = 6;
        $maxRows = 17;
        $row = 0;

        foreach ($evento->asistencias as $i => $asistencia) {
            $p = $asistencia->participant;

            // Si se llena la hoja, crear una nueva con el formato
            if ($row >= $maxRows) {
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplIdx);
                $pdf->SetFont('Arial', '', 8);
                $row = 0;
            }

            $y = $startY + ($row * $rowHeight);


            // === Datos del asistente ===
            $pdf->SetXY(32, $y);  // Nombres y Apellidos
            $pdf->Cell(61, 6, limitarTexto(trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')), 32), 0, 0, 'L');

            $pdf->SetXY(93, $y);  // Nº Identificación
            $pdf->Cell(38, 6, limitarTexto($p->document ?? '', 13), 0, 0, 'L');

            $pdf->SetXY(131, $y); // Código (role)
            $pdf->Cell(28, 6, limitarTexto($p->role ?? '', 13), 0, 0, 'L');

            $pdf->SetXY(159, $y); // Programa académico
            $pdf->Cell(34, 6, limitarTexto($p->program->name ?? '', 22), 0, 0, 'L');

            $pdf->SetXY(193, $y); // Teléfono (affiliation)
            $pdf->Cell(28, 6, limitarTexto($p->affiliation ?? '', 20), 0, 0, 'L');

            $pdf->SetXY(285, $y); // Hora de firma (registro de asistencia)
            $pdf->Cell(20, 6, Carbon::parse($asistencia->created_at)->format('h:i A'), 0, 0, 'C');




            // === Sexo ===
            if (($p->gender ?? '') === 'F') { $pdf->SetXY(178, $y); $pdf->Write(0, 'X'); }
            if (($p->gender ?? '') === 'M') { $pdf->SetXY(184, $y); $pdf->Write(0, 'X'); }

            // === Grupo priorizado ===
            $grupos = is_array($p->priority_group)
                ? $p->priority_group
                : explode(',', $p->priority_group ?? '');

            foreach ($grupos as $g) {
                switch (trim($g)) {
                    case 'E': $pdf->SetXY(192, $y); $pdf->Write(0, 'X'); break;
                    case 'D': $pdf->SetXY(198, $y); $pdf->Write(0, 'X'); break;
                    case 'V': $pdf->SetXY(204, $y); $pdf->Write(0, 'X'); break;
                    case 'C': $pdf->SetXY(210, $y); $pdf->Write(0, 'X'); break;
                    case 'H': $pdf->SetXY(216, $y); $pdf->Write(0, 'X'); break;
                }
            }

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
