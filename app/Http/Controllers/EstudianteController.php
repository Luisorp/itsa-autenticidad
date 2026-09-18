<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EstudianteController extends Controller
{
    public function index(Request $request)
    {
        $query = Estudiante::with('carrera')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $termino = $request->string('buscar')->toString();
            $query->where(fn ($q) => $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo', 'like', "%{$termino}%")
                ->orWhere('email', 'like', "%{$termino}%"));
        }

        if ($request->filled('carrera_id')) {
            $query->where('carrera_id', $request->integer('carrera_id'));
        }

        $estudiantes = $query->paginate(15)->withQueryString();
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();

        return view('estudiantes.index', compact('estudiantes', 'carreras'));
    }

    public function create()
    {
        $carreras = Carrera::where('activo', true)->orderBy('nombre')->get();
        $cuentas = User::where('rol', 'usuario')->where('activo', true)->whereDoesntHave('estudiante')->orderBy('name')->get();

        return view('estudiantes.create', compact('carreras', 'cuentas'));
    }

    public function store(Request $request)
    {
        Estudiante::create($this->validar($request));

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante registrado correctamente.');
    }

    public function edit(Estudiante $estudiante)
    {
        $carreras = Carrera::where(fn ($q) => $q->where('activo', true)->orWhere('id', $estudiante->carrera_id))
            ->orderBy('nombre')->get();
        $cuentas = User::where('rol', 'usuario')->where('activo', true)
            ->where(fn ($q) => $q->whereDoesntHave('estudiante')->orWhere('id', $estudiante->user_id))
            ->orderBy('name')->get();

        return view('estudiantes.edit', compact('estudiante', 'carreras', 'cuentas'));
    }

    public function update(Request $request, Estudiante $estudiante)
    {
        $estudiante->update($this->validar($request, $estudiante));

        return redirect()->route('estudiantes.index')->with('success', 'Estudiante actualizado correctamente.');
    }

    public function toggleActivo(Estudiante $estudiante)
    {
        $estudiante->update(['activo' => ! $estudiante->activo]);

        return back()->with('success', $estudiante->activo ? 'Estudiante activado.' : 'Estudiante desactivado.');
    }

    public function importar(Request $request)
    {
        $request->validate(['archivo' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $archivo = fopen($request->file('archivo')->getRealPath(), 'r');
        $primeraLinea = fgets($archivo);
        abort_if($primeraLinea === false, 422, 'El archivo CSV está vacío.');
        $delimitador = substr_count($primeraLinea, ';') > substr_count($primeraLinea, ',') ? ';' : ',';
        $encabezados = array_map($this->normalizarEncabezado(...), str_getcsv($primeraLinea, $delimitador));
        $indices = array_flip($encabezados);

        foreach (['codigo', 'nombre', 'carrera'] as $requerido) {
            if (! array_key_exists($requerido, $indices)) {
                fclose($archivo);

                return back()->withErrors(['archivo' => "Falta la columna obligatoria '{$requerido}'."]);
            }
        }

        $carreras = Carrera::pluck('id', 'codigo')->mapWithKeys(fn ($id, $codigo) => [Str::upper(trim($codigo)) => $id]);
        $filas = [];
        $errores = [];
        $codigos = [];
        $numeroLinea = 1;

        while (($columnas = fgetcsv($archivo, 0, $delimitador)) !== false) {
            $numeroLinea++;
            if (count(array_filter($columnas, fn ($valor) => trim((string) $valor) !== '')) === 0) {
                continue;
            }

            $valor = fn (string $columna) => trim((string) ($columnas[$indices[$columna] ?? -1] ?? ''));
            $codigo = $valor('codigo');
            $nombre = $valor('nombre');
            $codigoCarrera = Str::upper($valor('carrera'));
            $email = $valor('email') ?: null;
            $gestion = $valor('gestion') ?: ($valor('gestion_ingreso') ?: null);

            if ($codigo === '' || $nombre === '') {
                $errores[] = "Línea {$numeroLinea}: código y nombre son obligatorios.";

                continue;
            }
            if (isset($codigos[$codigo])) {
                $errores[] = "Línea {$numeroLinea}: el código {$codigo} está repetido en el archivo.";

                continue;
            }
            if (! isset($carreras[$codigoCarrera])) {
                $errores[] = "Línea {$numeroLinea}: la carrera {$codigoCarrera} no existe.";

                continue;
            }
            if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Línea {$numeroLinea}: el correo no es válido.";

                continue;
            }
            if ($gestion && (! ctype_digit((string) $gestion) || (int) $gestion < 2000 || (int) $gestion > (int) date('Y') + 1)) {
                $errores[] = "Línea {$numeroLinea}: la gestión no es válida.";

                continue;
            }

            $codigos[$codigo] = true;
            $filas[] = [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'email' => $email,
                'carrera_id' => $carreras[$codigoCarrera],
                'gestion_ingreso' => $gestion ? (int) $gestion : null,
                'activo' => true,
            ];
        }
        fclose($archivo);

        if ($errores !== []) {
            return back()->withErrors(['archivo' => implode(' ', array_slice($errores, 0, 8))]);
        }
        if ($filas === []) {
            return back()->withErrors(['archivo' => 'El archivo no contiene estudiantes para importar.']);
        }

        DB::transaction(function () use ($filas) {
            foreach ($filas as $fila) {
                Estudiante::updateOrCreate(['codigo' => $fila['codigo']], $fila);
            }
        });

        return back()->with('success', count($filas).' estudiantes importados o actualizados correctamente.');
    }

    public function plantilla()
    {
        $contenido = "codigo,nombre,email,carrera,gestion\nEST-001,Nombre completo,correo@ejemplo.com,SIS,".date('Y')."\n";

        return response($contenido, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_estudiantes.csv"',
        ]);
    }

    private function validar(Request $request, ?Estudiante $estudiante = null): array
    {
        return $request->validate([
            'codigo' => ['nullable', 'string', 'max:50', Rule::unique('estudiantes', 'codigo')->ignore($estudiante)],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'carrera_id' => ['required', Rule::exists('carreras', 'id')],
            'gestion_ingreso' => ['nullable', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('rol', 'usuario')->where('activo', true)),
                Rule::unique('estudiantes', 'user_id')->ignore($estudiante),
            ],
        ]);
    }

    private function normalizarEncabezado(string $encabezado): string
    {
        return Str::of($encabezado)->trim()->lower()->ascii()->replace([' ', '-'], '_')->toString();
    }
}
