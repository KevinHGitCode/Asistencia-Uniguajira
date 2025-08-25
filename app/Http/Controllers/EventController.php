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
        return view('events.list');
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
            ]);

            $validated['user_id'] = Auth::id();
            
            Event::create($validated);

            session()->flash('success', 'Evento creado exitosamente.');

            // Redirigir para evitar reenvío del formulario
            return redirect()->route('events.new')->with('success', 'Evento creado exitosamente.');
            
        } catch (\Exception $e) {
            // Log del error para debugging
            Log::error('Error creating event: ' . $e->getMessage());
            
            return back()->withInput()->withErrors(['error' => 'Hubo un error al crear el evento. Por favor, inténtalo de nuevo.']);
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
}