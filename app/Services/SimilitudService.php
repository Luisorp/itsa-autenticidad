<?php

namespace App\Services;

class SimilitudService
{
    public const TAMANO_SECUENCIA = 5;
    public const UMBRAL_FRAGMENTOS = 45;

    /** Retira rótulos y filas de plantilla, manteniendo la redacción del proyecto. */
    public function limpiarContenido(string $texto): string
    {
        $lineas = preg_split('/\R/u', mb_scrub($texto, 'UTF-8')) ?: [];
        foreach ($lineas as &$linea) {
            $normal = $this->normalizarTexto($linea);
            $normal = preg_replace('/^\s*\d+(?:\.\d+)+[.)]?\s*/u', '', $normal);
            if (preg_match('/^\s*(?:tabla|figura|grafico|ilustracion)\s*\d+\b/u', $normal)
                || preg_match('/^\s*fuente\s*:/u', $normal)
                || preg_match('/^\s*(?:sprint backlog|product backlog)\s*$/u', $normal)
                || preg_match('/^\s*(?:total\s*)?\d+(?:[.,]\d+)?\s*(?:horas?|dias?|semanas?|meses?)\s*(?:(?:total|tabla|fuente)\b.*)?$/u', $normal)
                || preg_match('/^\s*(?:prerrequisitos?|precondiciones?|postcondiciones?|actor(?:es)?|nombre (?:del )?caso de uso)\b/u', $normal)
                || preg_match('/^\s*(?:total|tiempo estimado|estimado|n[º°o]?\.? nombre|id tareas|resultado esperado)\s*[:\d\s]*$/u', $normal)) {
                // Mantiene un límite para no unir párrafos separados por una tabla.
                $linea = '';
            }
        }
        unset($linea);

        return trim(implode("\n", $lineas));
    }

    /**
     * Coincidencia textual simétrica: Dice sobre secuencias de cinco palabras.
     * Conserva orden, números y negaciones; no equivale a similitud semántica.
     */
    public function compararTextos(string $textoA, string $textoB): array
    {
        return $this->compararSecuencias(
            $this->secuencias($this->limpiarContenido($textoA)),
            $this->secuencias($this->limpiarContenido($textoB))
        );
    }

    protected array $stopwords = [
        'de', 'la', 'que', 'el', 'en', 'y', 'a', 'los', 'del', 'se', 'las', 'por', 'un', 'para', 'con', 'no', 'una', 'su', 'al',
        'lo', 'como', 'mas', 'pero', 'sus', 'le', 'ya', 'o', 'este', 'si', 'porque', 'esta', 'entre', 'cuando', 'muy', 'sin',
        'sobre', 'tambien', 'me', 'hasta', 'hay', 'donde', 'quien', 'desde', 'todo', 'nos', 'durante', 'todos', 'uno', 'les',
        'ni', 'contra', 'otros', 'ese', 'eso', 'ante', 'ellos', 'esto', 'mi', 'antes', 'algunos', 'unos', 'yo',
        'otro', 'otras', 'otra', 'tanto', 'esa', 'estos', 'mucho', 'quienes', 'nada', 'muchos', 'cual', 'poco', 'ella',
        'estar', 'estas', 'algunas', 'algo', 'nosotros', 'mis', 'tu', 'te', 'ti', 'tus', 'ellas', 'esos', 'esas', 'fue', 'ser',
        'son', 'es', 'han', 'ha', 'estan', 'para', 'the', 'and', 'of',
    ];

    /**
     * Convierte un texto en una lista de palabras normalizadas (sin tildes, sin mayúsculas,
     * sin palabras vacías). A diferencia de la versión anterior, aquí SÍ se repiten las
     * palabras (no se hace array_unique) porque necesitamos contar frecuencias.
     */
    
    public function tokenizar(string $texto): array
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u',
        ]);

        preg_match_all('/[a-z]+/u', $texto, $matches);
        $palabras = $matches[0];

        return array_values(array_filter($palabras, function ($p) {
            return mb_strlen($p) > 2 && ! in_array($p, $this->stopwords);
        }));
    }

    /**
     * Calcula el vector TF-IDF de cada documento, considerando a TODOS los documentos
     * recibidos como "el corpus" (necesario para calcular qué tan rara es cada palabra).
     *
     * @param  array<int, string>  $documentosTexto  ['id_documento' => 'texto del documento', ...]
     * @return array<int, array<string, float>> ['id_documento' => ['palabra' => peso, ...], ...]
     */
    public function calcularVectoresTfIdf(array $documentosTexto): array
    {
        if ($documentosTexto === []) {
            return [];
        }

        $tokensPorDocumento = [];
        foreach ($documentosTexto as $id => $texto) {
            $tokensPorDocumento[$id] = $this->tokenizar($this->limpiarContenido($texto ?? ''));
        }

        $totalDocumentos = count($tokensPorDocumento);

        // Frecuencia de documentos (df): en cuántos documentos distintos aparece cada palabra
        $df = [];
        foreach ($tokensPorDocumento as $tokens) {
            foreach (array_unique($tokens) as $palabra) {
                $df[$palabra] = ($df[$palabra] ?? 0) + 1;
            }
        }

        // IDF: entre más documentos tengan la palabra, menos peso aporta
        $idf = [];
        foreach ($df as $palabra => $frecuenciaDocumentos) {
            $idf[$palabra] = log(($totalDocumentos + 1) / ($frecuenciaDocumentos + 1)) + 1;
        }

        // Vector TF-IDF de cada documento
        $vectores = [];
        foreach ($tokensPorDocumento as $id => $tokens) {
            $totalPalabras = max(count($tokens), 1);
            $conteo = array_count_values($tokens);

            $vector = [];
            foreach ($conteo as $palabra => $frecuencia) {
                $tf = $frecuencia / $totalPalabras;
                $vector[$palabra] = $tf * ($idf[$palabra] ?? 0);
            }

            $vectores[$id] = $vector;
        }

        return $vectores;
    }

    /**
     * Similitud de coseno entre dos vectores TF-IDF, expresada como porcentaje 0-100.
     */
    public function similitudCoseno(array $vectorA, array $vectorB): float
    {
        $palabras = array_unique(array_merge(array_keys($vectorA), array_keys($vectorB)));

        $productoPunto = 0.0;
        $magnitudA = 0.0;
        $magnitudB = 0.0;

        foreach ($palabras as $palabra) {
            $a = $vectorA[$palabra] ?? 0;
            $b = $vectorB[$palabra] ?? 0;
            $productoPunto += $a * $b;
            $magnitudA += $a ** 2;
            $magnitudB += $b ** 2;
        }

        if ($magnitudA == 0 || $magnitudB == 0) {
            return 0.0;
        }

        $coseno = $productoPunto / (sqrt($magnitudA) * sqrt($magnitudB));

        return round(max(0, min(1, $coseno)) * 100, 2);
    }

    /**
     * Resume qué términos aportan a la similitud global calculada por TF-IDF.
     */
    public function explicarSimilitud(array $vectorA, array $vectorB): array
    {
        $terminosCompartidos = array_intersect(array_keys($vectorA), array_keys($vectorB));
        $aportes = [];

        foreach ($terminosCompartidos as $termino) {
            $aporte = ($vectorA[$termino] ?? 0) * ($vectorB[$termino] ?? 0);
            if ($aporte > 0) {
                $aportes[$termino] = $aporte;
            }
        }

        arsort($aportes);

        return [
            'terminos_compartidos' => count($aportes),
            'terminos_proyecto_a' => count($vectorA),
            'terminos_proyecto_b' => count($vectorB),
            'principales_terminos' => array_slice(array_keys($aportes), 0, 12),
            'umbral_fragmentos' => self::UMBRAL_FRAGMENTOS,
        ];
    }

    /**
     * Localiza fragmentos suficientemente extensos que comparten vocabulario relevante.
     * El resultado es orientativo: una coincidencia textual no demuestra plagio por sí sola.
     */
    public function encontrarCoincidencias(string $textoA, string $textoB, int $limite = 10): array
    {
        if ($limite <= 0) {
            return [];
        }
        $fragmentosA = $this->segmentarTexto($this->limpiarContenido($textoA));
        $fragmentosB = $this->segmentarTexto($this->limpiarContenido($textoB));
        $secuenciasA = array_map(fn ($texto) => $this->secuencias($texto), $fragmentosA);
        $secuenciasB = array_map(fn ($texto) => $this->secuencias($texto), $fragmentosB);
        $tokensPorB = array_map(fn ($texto) => $this->tokenizar($texto), $fragmentosB);
        $indiceB = [];
        foreach ($secuenciasB as $posicion => $secuencias) {
            foreach ($secuencias as $clave => $cantidad) {
                $indiceB[$clave][$posicion] = $cantidad;
            }
        }
        $candidatos = [];

        foreach ($fragmentosA as $indiceA => $fragmentoA) {
            $tokensA = $this->tokenizar($fragmentoA);
            if (count($tokensA) < 6) {
                continue;
            }

            // Solo compara bloques que realmente comparten secuencias, no todo con todo.
            $posibles = [];
            foreach ($secuenciasA[$indiceA] as $clave => $cantidad) {
                foreach ($indiceB[$clave] ?? [] as $posicion => $cantidadB) {
                    $posibles[$posicion] = ($posibles[$posicion] ?? 0) + min($cantidad, $cantidadB);
                }
            }
            foreach ($posibles as $posicionB => $cantidadCompartida) {
                if ($cantidadCompartida < 3) {
                    continue;
                }
                $fragmentoB = $fragmentosB[$posicionB];
                $tokensB = $tokensPorB[$posicionB];
                if (count($tokensB) < 6) {
                    continue;
                }

                $comunes = array_values(array_intersect(array_unique($tokensA), array_unique($tokensB)));
                if (count($comunes) < 5) {
                    continue;
                }

                $comparacion = $this->compararSecuencias($secuenciasA[$indiceA], $secuenciasB[$posicionB]);
                $porcentaje = $comparacion['porcentaje'];
                if ($porcentaje < self::UMBRAL_FRAGMENTOS || $comparacion['secuencias_compartidas'] < 3) {
                    continue;
                }

                $candidatos[] = [
                    'fragmento_a' => $fragmentoA,
                    'fragmento_b' => $fragmentoB,
                    'porcentaje' => $porcentaje,
                    'palabras_comunes' => array_slice($comunes, 0, 8),
                    'indice_a' => $indiceA,
                    'indice_b' => $posicionB,
                ];
            }
        }

        usort($candidatos, fn (array $a, array $b) => $b['porcentaje'] <=> $a['porcentaje']);

        $seleccionados = [];
        $usadosA = [];
        $usadosB = [];
        $pares = [];
        foreach ($candidatos as $candidato) {
            $clave = $this->normalizarTexto($candidato['fragmento_a']).'|'.$this->normalizarTexto($candidato['fragmento_b']);
            if (isset($usadosA[$candidato['indice_a']]) || isset($usadosB[$candidato['indice_b']]) || isset($pares[$clave])) {
                continue;
            }

            $usadosA[$candidato['indice_a']] = true;
            $usadosB[$candidato['indice_b']] = true;
            $pares[$clave] = true;
            unset($candidato['indice_a'], $candidato['indice_b']);
            $seleccionados[] = $candidato;

            if (count($seleccionados) >= $limite) {
                break;
            }
        }

        return $seleccionados;
    }

    private function segmentarTexto(string $texto): array
    {
        $bloques = preg_split('/(?<=[.!?])\s+|\R{2,}/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $fragmentos = [];

        foreach ($bloques as $bloque) {
            $bloque = preg_replace('/\s+/u', ' ', trim($bloque));
            if (mb_strlen($bloque) < 45) {
                continue;
            }

            if (mb_strlen($bloque) <= 600) {
                $fragmentos[] = $bloque;
                continue;
            }

            $palabras = preg_split('/\s+/u', $bloque, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach (array_chunk($palabras, 80) as $grupo) {
                $fragmento = implode(' ', $grupo);
                if (mb_strlen($fragmento) >= 45) {
                    $fragmentos[] = $fragmento;
                }
            }
        }

        return $fragmentos;
    }

    private function normalizarTexto(string $texto): string
    {
        return strtr(mb_strtolower(trim($texto), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        ]);
    }

    /** Frecuencias por secuencia, sin cruzar límites entre oraciones o párrafos. */
    private function secuencias(string $texto): array
    {
        $secuencias = [];
        $bloques = preg_split('/(?<=[.!?])\s+|\R\s*\R/u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($bloques as $bloque) {
            preg_match_all('/\p{L}+|\d+(?:[.,]\d+)*/u', $this->normalizarTexto($bloque), $palabras);
            $tokens = $palabras[0];
            for ($i = 0; $i <= count($tokens) - self::TAMANO_SECUENCIA; $i++) {
                $clave = implode(' ', array_slice($tokens, $i, self::TAMANO_SECUENCIA));
                $secuencias[$clave] = ($secuencias[$clave] ?? 0) + 1;
            }
        }

        return $secuencias;
    }

    private function compararSecuencias(array $a, array $b): array
    {
        $comunes = 0;
        foreach ($a as $clave => $cantidad) {
            $comunes += min($cantidad, $b[$clave] ?? 0);
        }
        $totalA = array_sum($a);
        $totalB = array_sum($b);

        return [
            'porcentaje' => $totalA + $totalB > 0 ? round(200 * $comunes / ($totalA + $totalB), 2) : 0.0,
            'secuencias_compartidas' => $comunes,
            'secuencias_proyecto_a' => $totalA,
            'secuencias_proyecto_b' => $totalB,
            'cobertura_proyecto_a' => $totalA > 0 ? round(100 * $comunes / $totalA, 2) : 0.0,
            'cobertura_proyecto_b' => $totalB > 0 ? round(100 * $comunes / $totalB, 2) : 0.0,
        ];
    }
}
