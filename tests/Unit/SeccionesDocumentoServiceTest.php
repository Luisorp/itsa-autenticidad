<?php

namespace Tests\Unit;

use App\Services\SeccionesDocumentoService;
use Tests\TestCase;

class SeccionesDocumentoServiceTest extends TestCase
{
    public function test_extrae_solo_las_secciones_autorizadas(): void
    {
        $texto = <<<'TEXT'
PORTADA
Datos institucionales que no deben analizarse.

ÍNDICE
Introducción ................................ 1
Marco teórico ............................... 5

1. RESUMEN
Este resumen presenta un sistema académico que analiza similitud documental de forma segura y eficiente.

2. INTRODUCCIÓN
La introducción explica el problema de autenticidad académica y el propósito de la plataforma institucional.

3. MARCO TEÓRICO
El marco teórico describe técnicas de TF-IDF y similitud coseno para comparar documentos académicos.

4. DESARROLLO
El desarrollo presenta la implementación del sistema y sus módulos de análisis documental.

5. CONCLUSIONES
Las conclusiones resumen los resultados obtenidos y las mejoras para el proceso académico.

BIBLIOGRAFÍA
Autor. Libro que tampoco debe incluirse en el análisis.
TEXT;

        $resultado = app(SeccionesDocumentoService::class)->extraer($texto);

        $this->assertSame([
            'Resumen',
            'Introducción',
            'Marco teórico',
            'Desarrollo o propuesta',
            'Conclusiones',
        ], $resultado['secciones']);
        $this->assertStringContainsString('TF-IDF', $resultado['texto']);
        $this->assertStringNotContainsString('Datos institucionales', $resultado['texto']);
        $this->assertStringNotContainsString('Libro que tampoco', $resultado['texto']);
    }

    public function test_indica_cuando_no_hay_secciones_autorizadas(): void
    {
        $resultado = app(SeccionesDocumentoService::class)->extraer('Portada e índice sin secciones de contenido autorizadas.');

        $this->assertSame('', $resultado['texto']);
        $this->assertSame([], $resultado['secciones']);
    }
}
