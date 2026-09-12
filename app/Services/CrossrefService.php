<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CrossrefService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscar(string $tipo, string $consulta): array
    {
        $consulta = trim($consulta);
        $cacheKey = 'crossref:'.sha1($tipo.'|'.mb_strtolower($consulta));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($tipo, $consulta) {
            $respuesta = $tipo === 'doi'
                ? $this->cliente()->get('/works/'.rawurlencode($this->normalizarDoi($consulta)))
                : $this->cliente()->get('/works', [
                    'query.bibliographic' => $consulta,
                    'rows' => 10,
                    'select' => 'DOI,title,author,published,container-title,type,URL,abstract',
                    ...$this->parametrosIdentificacion(),
                ]);

            $respuesta->throw();
            $mensaje = $respuesta->json('message', []);
            $items = $tipo === 'doi' ? [$mensaje] : ($mensaje['items'] ?? []);

            return array_values(array_map(fn (array $item) => $this->normalizarResultado($item), $items));
        });
    }

    private function cliente(): PendingRequest
    {
        $email = config('services.crossref.mailto');
        $agente = config('services.crossref.user_agent', 'Sello-ITSa/1.0');

        if ($email) {
            $agente .= ' (mailto:'.$email.')';
        }

        return Http::baseUrl(config('services.crossref.base_url', 'https://api.crossref.org'))
            ->acceptJson()
            ->withUserAgent($agente)
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 500, throw: false);
    }

    /** @return array<string, string> */
    private function parametrosIdentificacion(): array
    {
        $email = config('services.crossref.mailto');

        return $email ? ['mailto' => $email] : [];
    }

    private function normalizarDoi(string $doi): string
    {
        return preg_replace('#^(?:https?://(?:dx\.)?doi\.org/|doi:\s*)#i', '', trim($doi));
    }

    /** @return array<string, mixed> */
    private function normalizarResultado(array $item): array
    {
        $autores = array_map(function (array $autor) {
            return trim(($autor['given'] ?? '').' '.($autor['family'] ?? ''));
        }, $item['author'] ?? []);

        $partesFecha = $item['published']['date-parts'][0] ?? [];
        $fecha = $partesFecha ? implode('-', array_map(
            fn ($parte, $indice) => $indice === 0 ? $parte : str_pad((string) $parte, 2, '0', STR_PAD_LEFT),
            $partesFecha,
            array_keys($partesFecha)
        )) : null;

        $doi = $item['DOI'] ?? null;

        return [
            'fuente' => 'Crossref',
            'doi' => $doi,
            'titulo' => $item['title'][0] ?? 'Sin título registrado',
            'autores' => array_values(array_filter($autores)),
            'publicacion' => $item['container-title'][0] ?? null,
            'fecha' => $fecha,
            'tipo' => $item['type'] ?? null,
            'url' => $doi ? 'https://doi.org/'.$doi : ($item['URL'] ?? null),
            'resumen' => isset($item['abstract']) ? trim(strip_tags($item['abstract'])) : null,
            'citas' => null,
            'acceso_abierto' => null,
        ];
    }
}
