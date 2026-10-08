<?php

namespace App\Services;

class SeccionesDocumentoService
{
    public const ALGORITMO = 'Coincidencia textual; secuencias de 5 palabras (v3)';

    public const SECCIONES = [
        'resumen' => 'Resumen',
        'introduccion' => 'Introducción y planteamiento del problema',
        'desarrollo_propuesta' => 'Desarrollo o propuesta',
        'conclusiones' => 'Conclusiones y recomendaciones',
    ];

    /** @return array{texto: string, secciones: array<int, string>} */
    public function extraer(string $texto): array
    {
        $lineas = preg_split('/\R/u', mb_scrub($texto, 'UTF-8')) ?: [];
        $secciones = [];
        $actual = null;
        $capitulo = null;
        $teoria = false;

        foreach ($lineas as $linea) {
            $titulo = $this->normalizar($linea);

            // Las entradas de índices no son límites del contenido del documento.
            if (preg_match('/\.{2,}.*\d+\s*$/u', $linea)) {
                continue;
            }

            if (preg_match('/^(?:\d+\s*[.)-]\s*)?capitulo\s+(\d+|[ivxlcdm]+)\b(.*)$/', $titulo, $partes)) {
                $capitulo = ctype_digit($partes[1]) ? (int) $partes[1] : $this->numeroRomano($partes[1]);
                $teoria = $capitulo === 2;
                $actual = match (true) {
                    $capitulo === 2 => null,
                    $capitulo === 1 => 'introduccion',
                    str_contains($partes[2], 'conclus') => 'conclusiones',
                    default => 'desarrollo_propuesta',
                };
                continue;
            }

            $sinNumero = preg_replace('/^(?:\d+(?:\.\d+)*[.)]?|[ivxlcdm]+[.)])\s*/', '', $titulo);
            if ($this->esSeccionExcluida($sinNumero)) {
                $actual = null;
                $teoria = (bool) preg_match('/^(?:marco|fundamentacion|fundamento|bases|sustento|revision)/', $sinNumero);
                continue;
            }

            // Un subapartado del capítulo II nunca vuelve a habilitar el análisis.
            if ($capitulo === 2 || ($teoria && preg_match('/^\d+\.\d+/', $titulo))) {
                continue;
            }

            $clave = $this->clasificarSeccion($sinNumero);
            if ($clave !== null) {
                $actual = $clave;
                $teoria = false;
                continue;
            }

            // Quita números de página y encabezados institucionales repetidos.
            if (preg_match('/^(?:\d+|[ivxlcdm]+)$/', $titulo)
                || preg_match('/^(?:instituto tecnologico|carrera\b|r\.?\s*m\.?\b|fundado\b|(?:docente\s+)?tutor\b|postulante\b|autor(?:es)?\s*:)/', $titulo)) {
                continue;
            }

            if ($actual !== null) {
                $secciones[$actual][] = $linea;
            }
        }

        $contenido = [];
        foreach (self::SECCIONES as $clave => $nombre) {
            $parte = (new SimilitudService)->limpiarContenido(implode("\n", $secciones[$clave] ?? []));
            if (mb_strlen($parte, 'UTF-8') >= 40) {
                $contenido[$clave] = $parte;
            }
        }

        return [
            'texto' => implode("\n\n", $contenido),
            'secciones' => array_map(fn ($clave) => self::SECCIONES[$clave], array_keys($contenido)),
        ];
    }

    public function tieneContenidoAnalizable(string $texto): bool
    {
        return $this->extraer($texto)['texto'] !== '';
    }

    private function normalizar(string $linea): string
    {
        $linea = strtr(mb_strtolower(trim($linea), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        ]);

        return preg_replace('/\s+/u', ' ', str_replace(['–', '—'], '-', $linea));
    }

    private function esSeccionExcluida(string $titulo): bool
    {
        return (bool) preg_match('/^(?:marco (?:teorico(?:(?: y)? conceptual)?|conceptual|referencial)|fundamentacion teorica|fundamento teorico|sustento teorico|bases teoricas|revision (?:de la )?literatura|bibliografia|referencias(?: bibliograficas)?|anexos?|apendices?|agradecimientos|dedicatoria|(?:indice|contenido)(?: (?:general|de .+))?|tabla de contenido)[\s:.-]*$/', $titulo);
    }

    private function clasificarSeccion(string $titulo): ?string
    {
        return match (true) {
            (bool) preg_match('/^resumen[\s:.-]*$/', $titulo) => 'resumen',
            (bool) preg_match('/^(?:introduccion|planteamiento del problema)[\s:.-]*$/', $titulo) => 'introduccion',
            (bool) preg_match('/^(?:desarrollo(?: (?:del proyecto|de la propuesta))?|propuesta(?: tecnica)?|ingenieria del proyecto|implementacion)[\s:.-]*$/', $titulo) => 'desarrollo_propuesta',
            (bool) preg_match('/^(?:conclusiones(?: y recomendaciones)?|recomendaciones)[\s:.-]*$/', $titulo) => 'conclusiones',
            default => null,
        };
    }

    private function numeroRomano(string $numero): int
    {
        $valores = ['i' => 1, 'v' => 5, 'x' => 10, 'l' => 50, 'c' => 100, 'd' => 500, 'm' => 1000];
        $total = $anterior = 0;
        foreach (array_reverse(str_split($numero)) as $letra) {
            $valor = $valores[$letra];
            $total += $valor < $anterior ? -$valor : $valor;
            $anterior = $valor;
        }

        return $total;
    }
}
