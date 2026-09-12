<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use Illuminate\Http\Request;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = Carrera::orderBy('nombre')->paginate(10);
        return view('carreras.index', compact('carreras'));
    }

    public function create()
    {
        return view('carreras.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:carreras,nombre',
            'codigo' => 'nullable|string|max:20|unique:carreras,codigo',
        ]);

        Carrera::create($validated);

        return redirect()->route('carreras.index')->with('success', 'Carrera creada correctamente.');
    }

    public function edit(Carrera $carrera)
    {
        return view('carreras.edit', compact('carrera'));
    }

    public function update(Request $request, Carrera $carrera)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:carreras,nombre,' . $carrera->id,
            'codigo' => 'nullable|string|max:20|unique:carreras,codigo,' . $carrera->id,
        ]);

        $carrera->update($validated);

        return redirect()->route('carreras.index')->with('success', 'Carrera actualizada correctamente.');
    }

    public function toggleActivo(Carrera $carrera)
    {
        $carrera->update(['activo' => ! $carrera->activo]);

        return redirect()->route('carreras.index')->with('success', $carrera->activo ? 'Carrera activada.' : 'Carrera desactivada. Sus datos históricos se conservaron.');
    }
}
