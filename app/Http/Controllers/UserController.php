<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('users.users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return "proceso";
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $users = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the information view for the specified user.
     */
    public function information(string $id)
    {
        $user = User::findOrFail($id);
    
    // Obtener todos los eventos del usuario con información adicional
    $events = $user->events()
                  ->orderBy('date', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->get();
    
    // Calcular estadísticas
    $eventsCount = $events->count();
    
    // Contar eventos por estado (futuros, pasados)
    $now = now();
    $upcomingEvents = $events->filter(function ($event) use ($now) {
        return $event->date >= $now->toDateString();
    })->count();
    
    $pastEvents = $events->filter(function ($event) use ($now) {
        return $event->date < $now->toDateString();
    })->count();
    
    // Pasar todas las variables a la vista
    return view('users.information', compact(
        'user', 
        'events', 
        'eventsCount', 
        'upcomingEvents', 
        'pastEvents'
    ));
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
