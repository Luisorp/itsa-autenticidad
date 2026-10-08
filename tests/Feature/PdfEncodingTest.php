<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\ProyectoTitulacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class PdfEncodingTest extends TestCase
{
    use RefreshDatabase;

    public function test_subir_pdf_guarda_texto_utf8_valido(): void
    {
        [$usuario, $campos] = $this->prepararCarga();

        $this->actingAs($usuario)->post(route('proyectos.store'), $campos)
            ->assertRedirect(route('proyectos.index'))->assertSessionHasNoErrors();

        $this->verificarDocumento(ProyectoTitulacion::firstOrFail());
    }

    public function test_reemplazar_pdf_guarda_texto_utf8_valido(): void
    {
        [$usuario, $campos] = $this->prepararCarga();
        $proyecto = ProyectoTitulacion::create(collect($campos)->except('documento')->all());

        $this->actingAs($usuario)->put(route('proyectos.update', $proyecto), $campos)
            ->assertRedirect(route('proyectos.index'))->assertSessionHasNoErrors();

        $this->verificarDocumento($proyecto->fresh());
    }

    private function prepararCarga(): array
    {
        Storage::fake('public');
        $documento = $this->mock(Document::class);
        $documento->shouldReceive('getText')->once()->andReturn("Introducción académica \xED\xA0\xBE\xED\xB7\x91.\nConclusiones.");
        $this->mock(Parser::class)->shouldReceive('parseFile')->once()
            ->withArgs(fn ($ruta) => str_starts_with($ruta, Storage::disk('public')->path('documentos')))
            ->andReturn($documento);

        $usuario = User::factory()->create(['rol' => 'gestor', 'activo' => true]);
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $estudiante = Estudiante::create(['nombre' => 'Estudiante de prueba', 'carrera_id' => $carrera->id, 'activo' => true]);

        return [$usuario, [
            'titulo' => 'Proyecto con símbolos Unicode',
            'modalidad' => 'proyecto_grado',
            'carrera_id' => $carrera->id,
            'estudiante_id' => $estudiante->id,
            'anio' => 2026,
            'documento' => UploadedFile::fake()->create('unicode.pdf', 20, 'application/pdf'),
        ]];
    }

    private function verificarDocumento(ProyectoTitulacion $proyecto): void
    {
        $documento = $proyecto->documento;
        $this->assertNotNull($documento);
        $this->assertTrue(mb_check_encoding($documento->contenido_extraido, 'UTF-8'));
        $this->assertSame("Introducción académica \u{1F9D1}.\nConclusiones.", $documento->contenido_extraido);
        Storage::disk('public')->assertExists($documento->ruta_archivo);
        $this->assertSame('pendiente_analisis', $proyecto->estado);
    }
}
