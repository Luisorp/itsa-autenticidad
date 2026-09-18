<?php

namespace App\Services;

use App\Models\ProyectoTitulacion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class AnalisisExternoService
{
    public function __construct(
        private readonly CrossrefService $crossref,
        private readonly OpenAlexService $openAlex,
        private readonly SimilitudService $similitud,
    ) {}

    /** @return array{resultados: array<int, array<string, mixed>>, fuentesNoDisponibles: array<int, string>} */
    public function comparar(ProyectoTitulacion $proyecto): array
    {
        $publicaciones = [];
        $fuentesNoDisponibles = [];

        foreach (['Crossref' => $this->crossref, 'OpenAlex' => $this->openAlex] as $nombre => $servicio) {
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

        $vectores = $this->similitud->calcularVectoresTfIdf($corpus);
        $resultados = [];
        foreach ($publicaciones as $indice => $publicacion) {
            $textoExterno = $corpus['externo_'.$indice];
            $publicacion['porcentaje'] = $this->similitud->similitudCoseno(
                $vectores['proyecto'] ?? [],
                $vectores['externo_'.$indice] ?? []
            );
            $publicacion['coincidencias'] = $publicacion['resumen']
                ? $this->similitud->encontrarCoincidencias($corpus['proyecto'], $textoExterno, 3)
                : [];
            $publicacion['alcance'] = $publicacion['resumen'] ? 'Título y resumen' : 'Sólo título';
            $resultados[] = $publicacion;
        }

        usort($resultados, fn (array $a, array $b) => $b['porcentaje'] <=> $a['porcentaje']);

        return compact('resultados', 'fuentesNoDisponibles');
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
