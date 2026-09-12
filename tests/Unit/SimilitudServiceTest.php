<?php

namespace Tests\Unit;

use App\Services\SimilitudService;
use PHPUnit\Framework\TestCase;

class SimilitudServiceTest extends TestCase
{
    public function test_encuentra_fragmentos_textuales_semejantes(): void
    {
        $servicio = new SimilitudService;
        $textoA = 'El sistema permite registrar documentos académicos y comparar su contenido mediante técnicas de procesamiento de lenguaje natural.';
        $textoB = 'La plataforma permite registrar documentos académicos y comparar su contenido usando técnicas de procesamiento de lenguaje natural.';

        $coincidencias = $servicio->encontrarCoincidencias($textoA, $textoB);

        $this->assertNotEmpty($coincidencias);
        $this->assertGreaterThanOrEqual(45, $coincidencias[0]['porcentaje']);
        $this->assertArrayHasKey('fragmento_a', $coincidencias[0]);
        $this->assertArrayHasKey('palabras_comunes', $coincidencias[0]);
    }

    public function test_descarta_fragmentos_sin_relacion_suficiente(): void
    {
        $servicio = new SimilitudService;

        $coincidencias = $servicio->encontrarCoincidencias(
            'La arquitectura del software distribuye responsabilidades entre servicios independientes.',
            'La producción agrícola depende del clima y de la calidad mineral presente en el terreno.'
        );

        $this->assertSame([], $coincidencias);
    }

    public function test_explica_los_terminos_que_aportan_al_porcentaje_global(): void
    {
        $servicio = new SimilitudService;
        $vectores = $servicio->calcularVectoresTfIdf([
            1 => 'sistema académico documentos autenticidad comparación',
            2 => 'sistema documentos comparación digital',
        ]);

        $explicacion = $servicio->explicarSimilitud($vectores[1], $vectores[2]);

        $this->assertGreaterThan(0, $explicacion['terminos_compartidos']);
        $this->assertContains('sistema', $explicacion['principales_terminos']);
        $this->assertSame(45, $explicacion['umbral_fragmentos']);
    }
}
