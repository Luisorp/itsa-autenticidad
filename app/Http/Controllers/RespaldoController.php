<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;

class RespaldoController extends Controller
{
    protected function rutaRespaldos(): string
    {
        $ruta = storage_path('app/respaldos');

        if (! File::isDirectory($ruta)) {
            File::makeDirectory($ruta, 0755, true);
        }

        return $ruta;
    }

    public function index()
    {
        $archivos = collect(File::files($this->rutaRespaldos()))
            ->sortByDesc(fn ($archivo) => $archivo->getMTime())
            ->map(fn ($archivo) => [
                'nombre' => $archivo->getFilename(),
                'tamano' => round($archivo->getSize() / 1024, 1),
                'fecha' => date('d/m/Y H:i', $archivo->getMTime()),
            ]);

        return view('respaldos.index', compact('archivos'));
    }

    public function generar()
    {
        $nombreArchivo = 'respaldo_' . now()->format('Y-m-d_His') . '.sql';
        $ruta = $this->rutaRespaldos() . DIRECTORY_SEPARATOR . $nombreArchivo;

        $tablas = ['carreras', 'users', 'proyectos_titulacion', 'documentos', 'comparaciones', 'reportes'];

        $sql = "-- Respaldo generado el " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tablas as $tabla) {
            $filas = DB::table($tabla)->get();

            if ($filas->isEmpty()) {
                continue;
            }

            $sql .= "-- Tabla: {$tabla}\n";
            $sql .= "TRUNCATE TABLE `{$tabla}`;\n";

            foreach ($filas as $fila) {
                $datos = (array) $fila;
                $columnas = array_keys($datos);
                $valores = array_map(function ($valor) {
                    return is_null($valor) ? 'NULL' : "'" . addslashes($valor) . "'";
                }, array_values($datos));

                $sql .= "INSERT INTO `{$tabla}` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $valores) . ");\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($ruta, $sql);

        return redirect()->route('respaldos.index')->with('success', 'Respaldo generado correctamente.');
    }

    public function descargar(string $archivo)
    {
        $ruta = $this->rutaRespaldos() . DIRECTORY_SEPARATOR . basename($archivo);

        if (! File::exists($ruta)) {
            abort(404);
        }

        return response()->download($ruta);
    }
}