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
            'Introducción y planteamiento del problema',
            'Desarrollo o propuesta',
            'Conclusiones y recomendaciones',
        ], $resultado['secciones']);
        $this->assertStringNotContainsString('TF-IDF', $resultado['texto']);
        $this->assertStringNotContainsString('Datos institucionales', $resultado['texto']);
        $this->assertStringNotContainsString('Libro que tampoco', $resultado['texto']);
    }

    public function test_indica_cuando_no_hay_secciones_autorizadas(): void
    {
        $resultado = app(SeccionesDocumentoService::class)->extraer('Portada e índice sin secciones de contenido autorizadas.');

        $this->assertSame('', $resultado['texto']);
        $this->assertSame([], $resultado['secciones']);
    }

    public function test_excluye_capitulo_dos_completo_con_encabezados_reales(): void
    {
        foreach (['2.CAPÍTULO II - MARCO TEÓRICO CONCEPTUAL', 'CAPITULO 2', "CAPÍTULO II MARCO\nTEÓRICO CONCEPTUAL"] as $encabezado) {
            $texto = "TUTOR: Freddy Ledezma Higuera\nNoviembre, 2025 Sacaba, Cochabamba\nÍNDICE\n1. CAPÍTULO I - INTRODUCCIÓN .......... 1\n2. CAPÍTULO II .......... 10\n3. CAPÍTULO III .......... 30\n1. CAPÍTULO I - INTRODUCCIÓN\nEl sistema resuelve necesidades propias del negocio mediante un proceso original de registro de pedidos.\n{$encabezado}\nUn Scrum Master dirige prácticas comunes del equipo.\n2.1. DESARROLLO\nEsta definición teórica también debe quedar fuera aunque se llame desarrollo.\n3. CAPÍTULO III - INGENIERÍA DEL PROYECTO\nSe implementó un módulo específico para calcular costos de los pedidos y registrar entregas.\n4. CAPÍTULO IV - CONCLUSIONES Y RECOMENDACIONES\nEl negocio redujo errores en sus pedidos tras validar la propuesta implementada.\nBIBLIOGRAFÍA\nReferencia que no debe formar parte del análisis.";
            $resultado = app(SeccionesDocumentoService::class)->extraer($texto);
            $this->assertStringContainsString('necesidades propias', $resultado['texto']);
            $this->assertStringContainsString('módulo específico', $resultado['texto']);
            $this->assertStringContainsString('redujo errores', $resultado['texto']);
            foreach (['Freddy', 'Noviembre', 'Scrum', 'definición teórica', 'Referencia', 'CAPÍTULO'] as $excluido) {
                $this->assertStringNotContainsString($excluido, $resultado['texto']);
            }
        }
    }

    public function test_reconoce_nombres_alternativos_del_marco_teorico(): void
    {
        foreach (['MARCO CONCEPTUAL', 'MARCO REFERENCIAL', 'FUNDAMENTACIÓN TEÓRICA', 'BASES TEÓRICAS', 'REVISIÓN DE LA LITERATURA', 'MARCO TEÓRICO Y CONCEPTUAL'] as $encabezado) {
            $texto = "INTRODUCCIÓN\nEste contenido original explica el problema del proyecto y sus objetivos específicos.\n{$encabezado}\nDefiniciones comunes que no deben influir en el porcentaje final.\n2.1. INTRODUCCIÓN\nOtro subapartado teórico que debe permanecer excluido.\n3. DESARROLLO\nEsta implementación específica resuelve el problema mediante funciones propias del proyecto.\nANEXOS\nMaterial adjunto que tampoco debe influir en el análisis.";
            $resultado = app(SeccionesDocumentoService::class)->extraer($texto);
            $this->assertStringContainsString('contenido original', $resultado['texto']);
            $this->assertStringContainsString('implementación específica', $resultado['texto']);
            $this->assertStringNotContainsString('Definiciones comunes', $resultado['texto']);
            $this->assertStringNotContainsString('subapartado teórico', $resultado['texto']);
            $this->assertStringNotContainsString('Material adjunto', $resultado['texto']);
        }
    }
}
