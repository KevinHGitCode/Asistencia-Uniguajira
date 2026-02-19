<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Dependency;
use Illuminate\Http\Request;

class DependencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // obtener dependencias con sus eventos relacionada
        $dependencies = Dependency::with('areas')
            ->withCount(['areas', 'events'])
            ->get();

        return view('administration.dependencies.index', compact('dependencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dependency $dependency)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dependency $dependency)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dependency $dependency)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dependency $dependency)
    {
        //
    }
}
