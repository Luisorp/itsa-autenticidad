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

    public function test_omite_totales_y_rotulos_del_ejemplo_del_sprint(): void
    {
        $servicio = new SimilitudService;
        $a = '50 horas Total 160 horas Tabla 9: Sprint Backlog';
        $b = '100 horas TOTAL 200 horas Tabla 7 Sprint Backlog Fuente: Elaboración Propia';

        $this->assertSame('', $servicio->limpiarContenido($a));
        $this->assertSame('', $servicio->limpiarContenido($b));
        $this->assertSame(0.0, $servicio->compararTextos($a, $b)['porcentaje']);
        $this->assertSame([], $servicio->encontrarCoincidencias($a, $b));
    }

    public function test_cifras_y_negaciones_no_desaparecen_de_la_comparacion(): void
    {
        $servicio = new SimilitudService;
        $a = 'El módulo permite procesar 50 solicitudes de inscripción para estudiantes nuevos y generar recibos personalizados.';
        $b = 'El módulo permite procesar 100 solicitudes de inscripción para estudiantes nuevos y generar recibos personalizados.';
        $c = 'El módulo no permite procesar 50 solicitudes de inscripción para estudiantes nuevos y generar recibos personalizados.';

        $this->assertLessThan(100, $servicio->compararTextos($a, $b)['porcentaje']);
        $this->assertLessThan(100, $servicio->compararTextos($a, $c)['porcentaje']);
        $texto = '50 horas de capacitación permiten preparar a los docentes para registrar correctamente los resultados de cada evaluación.';
        $this->assertSame($texto, $servicio->limpiarContenido($texto));
    }

    public function test_el_mismo_vocabulario_en_otro_orden_no_da_una_coincidencia_alta(): void
    {
        $servicio = new SimilitudService;
        $a = 'clientes facturas cuentas declaraciones auditorías impuestos recibos movimientos registros reportes ingresos egresos';
        $b = 'egresos ingresos reportes registros movimientos recibos impuestos auditorías declaraciones cuentas facturas clientes';

        $this->assertSame(0.0, $servicio->compararTextos($a, $b)['porcentaje']);
        $this->assertSame([], $servicio->encontrarCoincidencias($a, $b));
    }

    public function test_una_copia_real_de_redaccion_se_conserva_como_evidencia(): void
    {
        $servicio = new SimilitudService;
        $texto = 'La consultora automatizó el envío de declaraciones mensuales mediante una validación del identificador tributario y una confirmación individual para cada cliente.';

        $this->assertSame(100.0, $servicio->compararTextos($texto, $texto)['porcentaje']);
        $this->assertSame(100.0, $servicio->encontrarCoincidencias($texto, $texto)[0]['porcentaje']);
        $this->assertSame([], $servicio->encontrarCoincidencias($texto, $texto, 0));
    }

    public function test_el_porcentaje_es_simetrico_y_no_cruza_filas_excluidas(): void
    {
        $servicio = new SimilitudService;
        $a = 'La consultora automatizó el envío de declaraciones mensuales mediante un módulo especializado para cada cliente.';
        $b = "La consultora automatizó el envío de declaraciones mensuales mediante un módulo especializado para cada cliente.\nTambién integró alertas para revisar pagos pendientes y confirmar documentos aprobados.";

        $this->assertSame($servicio->compararTextos($a, $b)['porcentaje'], $servicio->compararTextos($b, $a)['porcentaje']);
        $this->assertGreaterThan(0, $servicio->compararTextos($a, $b)['porcentaje']);
        $this->assertLessThan(100, $servicio->compararTextos($a, $b)['porcentaje']);
        $this->assertSame(0.0, $servicio->compararTextos("uno dos tres\nTabla 9: Sprint Backlog\ncuatro cinco seis", 'uno dos tres cuatro cinco seis')['porcentaje']);
    }

    public function test_no_muestra_repetido_el_mismo_par_de_fragmentos(): void
    {
        $servicio = new SimilitudService;
        $texto = 'La consultora automatizó el envío de declaraciones mensuales mediante una validación del identificador tributario y una confirmación individual para cada cliente.';
        $this->assertCount(1, $servicio->encontrarCoincidencias($texto."\n\n".$texto, $texto."\n\n".$texto));
    }
}
