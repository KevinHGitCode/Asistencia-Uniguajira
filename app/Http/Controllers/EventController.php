<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            return redirect()->route('events.new')->with('success', 'Evento creado exitosamente.');
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
        //
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
}
