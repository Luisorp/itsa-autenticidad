<?php

namespace App\Http\Controllers;

use App\Models\ProyectoTitulacion;
use App\Services\CrossrefService;
use App\Services\OpenAlexService;
use App\Services\SimilitudService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class AnalisisExternoController extends Controller
{
    public function show(ProyectoTitulacion $proyecto)
    {
        $this->validarProyecto($proyecto);

        return view('analisis.externo', compact('proyecto'));
    }

    public function analizar(
        ProyectoTitulacion $proyecto,
        CrossrefService $crossref,
        OpenAlexService $openAlex,
        SimilitudService $similitud
    ) {
        $this->validarProyecto($proyecto);

        $publicaciones = [];
        $fuentesNoDisponibles = [];
        foreach (['Crossref' => $crossref, 'OpenAlex' => $openAlex] as $nombre => $servicio) {
            try {
                $publicaciones = [...$publicaciones, ...$servicio->buscar('titulo', $proyecto->titulo)];
            } catch (ConnectionException|RequestException) {
                $fuentesNoDisponibles[] = $nombre;
            }
        }

        $publicaciones = $this->eliminarDuplicados($publicaciones);
        $corpus = ['proyecto' => $proyecto->documento->contenido_extraido];
        foreach ($publicaciones as $indice => $publicacion) {
            $corpus['externo_'.$indice] = $this->textoComparable($publicacion);
        }

        $vectores = $similitud->calcularVectoresTfIdf($corpus);
        $resultados = [];
        foreach ($publicaciones as $indice => $publicacion) {
            $textoExterno = $corpus['externo_'.$indice];
            $publicacion['porcentaje'] = $similitud->similitudCoseno(
                $vectores['proyecto'] ?? [],
                $vectores['externo_'.$indice] ?? []
            );
            $publicacion['coincidencias'] = $publicacion['resumen']
                ? $similitud->encontrarCoincidencias($corpus['proyecto'], $textoExterno, 3)
                : [];
            $publicacion['alcance'] = $publicacion['resumen'] ? 'Título y resumen' : 'Sólo título';
            $resultados[] = $publicacion;
        }

        usort($resultados, fn (array $a, array $b) => $b['porcentaje'] <=> $a['porcentaje']);

        return view('analisis.externo', compact('proyecto', 'resultados', 'fuentesNoDisponibles'));
    }

    private function validarProyecto(ProyectoTitulacion $proyecto): void
    {
        abort_unless($proyecto->activo, 404);
        $proyecto->loadMissing(['documento', 'estudiante', 'carrera']);
        abort_if(
            ! $proyecto->documento || trim((string) $proyecto->documento->contenido_extraido) === '',
            422,
            'El proyecto no tiene texto extraído disponible para el análisis externo.'
        );
    }

    /** @param array<int, array<string, mixed>> $publicaciones */
    private function eliminarDuplicados(array $publicaciones): array
    {
        $unicas = [];
        foreach ($publicaciones as $publicacion) {
            $clave = mb_strtolower($publicacion['doi'] ?: $publicacion['titulo']);
            if (! isset($unicas[$clave])) {
                $unicas[$clave] = $publicacion;
            } elseif ($unicas[$clave]['resumen'] === null && $publicacion['resumen'] !== null) {
                $unicas[$clave] = $publicacion;
            }
        }

        return array_values($unicas);
    }

    /** @param array<string, mixed> $publicacion */
    private function textoComparable(array $publicacion): string
    {
        return trim($publicacion['titulo'].'. '.($publicacion['resumen'] ?? ''));
    }
}
