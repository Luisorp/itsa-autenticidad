<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        $query = Docente::with('carreras')->orderBy('nombre');
        if ($request->filled('buscar')) {
            $termino = $request->string('buscar')->toString();
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo', 'like', "%{$termino}%")
                ->orWhere('email', 'like', "%{$termino}%")
                ->orWhere('especialidad', 'like', "%{$termino}%"));
        }

        $docentes = $query->paginate(15)->withQueryString();

        return view('docentes.index', compact('docentes'));
    }

    public function create()
    {
        $cuentas = User::where('rol', 'gestor')->where('activo', true)->whereDoesntHave('docente')->orderBy('name')->get();
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();

        return view('docentes.create', compact('cuentas', 'carreras'));
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        $carreraIds = $validated['carrera_ids'];
        unset($validated['carrera_ids']);

        DB::transaction(function () use ($validated, $carreraIds) {
            $docente = Docente::create($validated);
            $docente->carreras()->sync($carreraIds);
        });

        return redirect()->route('docentes.index')->with('success', 'Docente registrado correctamente.');
    }

    public function edit(Docente $docente)
    {
        $cuentas = User::where('rol', 'gestor')->where('activo', true)
            ->where(fn ($q) => $q->whereDoesntHave('docente')->orWhere('id', $docente->user_id))
            ->orderBy('name')->get();
        $carreras = Carrera::where('activo', true)
            ->orWhereHas('docentes', fn ($query) => $query->where('docentes.id', $docente->id))
            ->orderBy('nombre')->get();
        $docente->load('carreras');

        return view('docentes.edit', compact('docente', 'cuentas', 'carreras'));
    }

    public function update(Request $request, Docente $docente)
    {
        $validated = $this->validar($request, $docente);
        $carreraIds = $validated['carrera_ids'];
        unset($validated['carrera_ids']);

        DB::transaction(function () use ($docente, $validated, $carreraIds) {
            $docente->update($validated);
            $docente->carreras()->sync($carreraIds);
        });

        return redirect()->route('docentes.index')->with('success', 'Docente actualizado correctamente.');
    }

    public function toggleActivo(Docente $docente)
    {
        $docente->update(['activo' => ! $docente->activo]);

        return back()->with('success', $docente->activo ? 'Docente activado.' : 'Docente desactivado.');
    }

    private function validar(Request $request, ?Docente $docente = null): array
    {
        return $request->validate([
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('docentes', 'codigo')->ignore($docente)],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'carrera_ids' => ['required', 'array', 'min:1'],
            'carrera_ids.*' => ['integer', Rule::exists('carreras', 'id')],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'gestor')->where('activo', true)),
                Rule::unique('docentes', 'user_id')->ignore($docente),
            ],
        ]);
    }
}
