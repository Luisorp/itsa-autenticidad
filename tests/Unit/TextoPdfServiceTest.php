<?php

namespace Tests\Unit;

use App\Services\TextoPdfService;
use PHPUnit\Framework\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class TextoPdfServiceTest extends TestCase
{
    public function test_preserva_texto_unicode_valido(): void
    {
        $texto = "Introducción: análisis académico, año 2026. 🗑\nConclusiones.";
        $this->assertSame($texto, $this->servicio($texto)->extraer('prueba.pdf'));
    }

    public function test_recupera_simbolos_cesu8_y_repara_bytes_invalidos(): void
    {
        $texto = "Introducción \xED\xA0\xBE\xED\xB7\x91 y \xED\xA0\xBD\xED\xB8\x80.\nConclusión \xFF";
        $resultado = $this->servicio($texto)->extraer('prueba.pdf');

        $this->assertTrue(mb_check_encoding($resultado, 'UTF-8'));
        $this->assertStringContainsString("Introducción \u{1F9D1} y \u{1F600}.", $resultado);
        $this->assertStringContainsString('Conclusión ', $resultado);
    }

    public function test_un_pdf_no_extraible_conserva_el_comportamiento_anterior(): void
    {
        $parser = $this->createMock(Parser::class);
        $parser->method('parseFile')->willThrowException(new \Exception('PDF sin texto'));

        $this->assertNull((new TextoPdfService($parser))->extraer('prueba.pdf'));
    }

    private function servicio(string $texto): TextoPdfService
    {
        $documento = $this->createMock(Document::class);
        $documento->method('getText')->willReturn($texto);
        $parser = $this->createMock(Parser::class);
        $parser->method('parseFile')->willReturn($documento);

        return new TextoPdfService($parser);
    }
}
