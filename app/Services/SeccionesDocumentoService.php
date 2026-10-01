<?php

namespace App\Services;

class SeccionesDocumentoService
{
    /** @var array<string, string> */
    public const SECCIONES = [
        'resumen' => 'Resumen',
        'introduccion' => 'Introducción',
        'marco_teorico' => 'Marco teórico',
        'desarrollo_propuesta' => 'Desarrollo o propuesta',
        'conclusiones' => 'Conclusiones',
    ];

    /**
     * Extrae únicamente las secciones institucionales autorizadas para el análisis.
     * Los encabezados deben ocupar una línea propia, como ocurre habitualmente al
     * extraer texto de un PDF académico.
     *
     * @return array{texto: string, secciones: array<int, string>}
     */
    public function extraer(string $texto): array
    {
        $texto = preg_replace("/\r\n?|\x{00A0}/u", "\n", $texto) ?? $texto;
        $encabezados = $this->buscarEncabezados($texto);
        $secciones = [];

        foreach ($encabezados as $indice => $encabezado) {
            $clave = $this->clasificarSeccion($encabezado['titulo']);
            if (! $clave) {
                continue;
            }

            $inicio = $encabezado['fin'];
            $fin = $encabezados[$indice + 1]['inicio'] ?? mb_strlen($texto, 'UTF-8');
            $contenido = trim(mb_substr($texto, $inicio, $fin - $inicio, 'UTF-8'));

            if (mb_strlen($contenido, 'UTF-8') >= 40) {
                $secciones[$clave] = trim(($secciones[$clave] ?? '')."\n\n".$contenido);
            }
        }

        $ordenadas = [];
        foreach (self::SECCIONES as $clave => $nombre) {
            if (! empty($secciones[$clave])) {
                $ordenadas[$clave] = $secciones[$clave];
            }
        }

        return [
            'texto' => implode("\n\n", $ordenadas),
            'secciones' => array_map(fn (string $clave) => self::SECCIONES[$clave], array_keys($ordenadas)),
        ];
    }

    public function tieneContenidoAnalizable(string $texto): bool
    {
        return $this->extraer($texto)['texto'] !== '';
    }

    /** @return array<int, array{titulo: string, inicio: int, fin: int}> */
    private function buscarEncabezados(string $texto): array
    {
        $patron = '/^[\h]*(?:(?:\d+(?:\.\d+)*|[IVXLCDM]+)[.)]?[\h]+)?'
            .'(resumen|introducci[oó]n|marco[\h]+te[oó]rico|metodolog[ií]a|desarrollo|propuesta|conclusiones|recomendaciones|bibliograf[ií]a|referencias|anexos?|ap[eé]ndices?|agradecimientos|dedicatoria|[ií]ndice|contenido)'
            .'[\h:.-]*$/miu';

        preg_match_all($patron, $texto, $coincidencias, PREG_OFFSET_CAPTURE);
        $encabezados = [];
        foreach ($coincidencias[0] as $indice => $coincidencia) {
            $linea = $coincidencia[0];
            $inicioBytes = $coincidencia[1];
            $inicio = mb_strlen(substr($texto, 0, $inicioBytes), 'UTF-8');
            $fin = $inicio + mb_strlen($linea, 'UTF-8');
            $encabezados[] = [
                'titulo' => $coincidencias[1][$indice][0],
                'inicio' => $inicio,
                'fin' => $fin,
            ];
        }

        return $encabezados;
    }

    private function clasificarSeccion(string $titulo): ?string
    {
        $titulo = strtr(mb_strtolower(trim($titulo), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);

        return match (true) {
            str_starts_with($titulo, 'resumen') => 'resumen',
            str_starts_with($titulo, 'introduccion') => 'introduccion',
            str_starts_with($titulo, 'marco teorico') => 'marco_teorico',
            str_starts_with($titulo, 'desarrollo'), str_starts_with($titulo, 'propuesta') => 'desarrollo_propuesta',
            str_starts_with($titulo, 'conclusiones') => 'conclusiones',
            default => null,
        };
    }
}
