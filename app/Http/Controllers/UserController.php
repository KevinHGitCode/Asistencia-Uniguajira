<?php

namespace App\Http\Controllers;

use App\Models\Dependency;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // obtener usuarios con su dependencia relacionada
        $users = User::with('dependency')->get();
        return view('users.users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // pluck debe recibir (value, key)
        $dependencies = Dependency::orderBy('name')->pluck('name', 'id')->toArray();
        $roles = ['admin' => 'Administrador', 'user' => 'Usuario'];

        return view('users.create', compact('dependencies', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'dependency_id' => [
                Rule::requiredIf(fn () => $request->input('role') !== 'admin'),
                'nullable',
                'integer',
                'exists:dependencies,id',
            ],
        ]);

        // Si es administrador, no asociar dependencia
        if (($validated['role'] ?? '') === 'admin') {
            $validated['dependency_id'] = null;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'dependency_id' => $validated['dependency_id'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the information view for the specified user.
     */
    public function information(string $id)
    {
        // Cargar la dependencia relacionada para mostrarla en la vista si aplica
        $user = User::with('dependency')->findOrFail($id);

        // Obtener eventos paginados
        $events = $user->events()
                  ->orderBy('date', 'desc')
                  ->orderBy('created_at', 'desc')
                  ->paginate(6); // <-- aquí limitamos a 6 por página

        // Calcular estadísticas (usando todos los eventos, no solo la página actual)
        $allEvents = $user->events()->get();
        $eventsCount = $allEvents->count();

        $now = now();
        $upcomingEvents = $allEvents->where('date', '>=', $now->toDateString())->count();
        $pastEvents     = $allEvents->where('date', '<', $now->toDateString())->count();

        return view('users.information', compact(
          'user',
          'events',       // paginados
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
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $password = request('password');
        $authUser = Auth::user();

        if (!Hash::check($password, $authUser->password)) {
            return redirect()->back()->withErrors(['password' => 'La contraseña es incorrecta.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
    }
}
