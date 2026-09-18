<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $roles = [
            'administrador' => 'Administradores',
            'gestor' => 'Gestores',
            'usuario' => 'Usuarios',
        ];
        $rolActual = array_key_exists($request->string('rol')->toString(), $roles)
            ? $request->string('rol')->toString()
            : 'usuario';
        $conteos = User::selectRaw('rol, COUNT(*) as total')->groupBy('rol')->pluck('total', 'rol');
        $usuarios = User::with('carrera')
            ->where('rol', $rolActual)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios', 'roles', 'rolActual', 'conteos'));
    }

    public function create()
    {
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();

        return view('usuarios.create', compact('carreras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'carrera_id' => ['nullable', Rule::exists('carreras', 'id')->where(fn ($query) => $query->where('activo', true))],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'carrera_id' => $validated['carrera_id'] ?? null,
        ]);

        return redirect()->route('usuarios.index', ['rol' => $validated['rol']])->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $carreras = Carrera::where(function ($query) use ($usuario) {
            $query->where('activo', true)->orWhere('id', $usuario->carrera_id);
        })->orderBy('nombre')->get();

        return view('usuarios.edit', compact('usuario', 'carreras'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$usuario->id,
            'rol' => ['required', Rule::in(array_keys(User::ROLES))],
            'carrera_id' => 'nullable|exists:carreras,id',
        ]);

        $usuario->update($validated);

        return redirect()->route('usuarios.index', ['rol' => $validated['rol']])->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index', ['rol' => $usuario->rol])->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return redirect()->route('usuarios.index', ['rol' => $usuario->rol])->with('success', $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }
}
