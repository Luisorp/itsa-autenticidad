<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenAlexService
{
    /** @return array<int, array<string, mixed>> */
    public function buscar(string $tipo, string $consulta): array
    {
        $consulta = trim($consulta);
        $cacheKey = 'openalex:'.sha1($tipo.'|'.mb_strtolower($consulta));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($tipo, $consulta) {
            $parametros = [
                'select' => 'id,doi,title,authorships,primary_location,publication_date,type,open_access,abstract_inverted_index,cited_by_count',
                ...$this->parametrosAutenticacion(),
            ];

            $respuesta = $tipo === 'doi'
                ? $this->cliente()->get('/works/doi:'.rawurlencode($this->normalizarDoi($consulta)), $parametros)
                : $this->cliente()->get('/works', [
                    'search' => $consulta,
                    'per_page' => 10,
                    ...$parametros,
                ]);

            $respuesta->throw();
            $items = $tipo === 'doi' ? [$respuesta->json()] : $respuesta->json('results', []);

            return array_values(array_map(fn (array $item) => $this->normalizarResultado($item), $items));
        });
    }

    private function cliente(): PendingRequest
    {
        return Http::baseUrl(config('services.openalex.base_url', 'https://api.openalex.org'))
            ->acceptJson()
            ->withUserAgent(config('services.openalex.user_agent', 'Sello-ITSa/1.0'))
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 500, throw: false);
    }

    /** @return array<string, string> */
    private function parametrosAutenticacion(): array
    {
        $apiKey = config('services.openalex.api_key');

        return $apiKey ? ['api_key' => $apiKey] : [];
    }

    private function normalizarDoi(string $doi): string
    {
        return preg_replace('#^(?:https?://(?:dx\.)?doi\.org/|doi:\s*)#i', '', trim($doi));
    }

    /** @return array<string, mixed> */
    private function normalizarResultado(array $item): array
    {
        $doi = isset($item['doi']) ? $this->normalizarDoi($item['doi']) : null;
        $autores = array_values(array_filter(array_map(
            fn (array $autoria) => $autoria['author']['display_name'] ?? null,
            $item['authorships'] ?? []
        )));

        return [
            'fuente' => 'OpenAlex',
            'doi' => $doi,
            'titulo' => $item['title'] ?? 'Sin título registrado',
            'autores' => $autores,
            'publicacion' => $item['primary_location']['source']['display_name'] ?? null,
            'fecha' => $item['publication_date'] ?? null,
            'tipo' => $item['type'] ?? null,
            'url' => $doi
                ? 'https://doi.org/'.$doi
                : ($item['primary_location']['landing_page_url'] ?? $item['id'] ?? null),
            'resumen' => $this->reconstruirResumen($item['abstract_inverted_index'] ?? null),
            'citas' => $item['cited_by_count'] ?? null,
            'acceso_abierto' => $item['open_access']['is_oa'] ?? null,
        ];
    }

    /** @param array<string, array<int, int>>|null $indice */
    private function reconstruirResumen(?array $indice): ?string
    {
        if (! $indice) {
            return null;
        }

        $palabras = [];
        foreach ($indice as $palabra => $posiciones) {
            foreach ($posiciones as $posicion) {
                $palabras[$posicion] = $palabra;
            }
        }
        ksort($palabras);

        return mb_strimwidth(implode(' ', $palabras), 0, 1200, '…');
    }
}
