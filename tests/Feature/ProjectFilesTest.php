<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Documento;
use App\Models\ProyectoTitulacion;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_open_project_edit_form(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();

        $this->actingAs($administrador)
            ->get(route('proyectos.edit', $proyecto))
            ->assertOk()
            ->assertSee($proyecto->titulo);
    }

    public function test_authenticated_user_can_view_a_project_pdf(): void
    {
        Storage::fake('public');
        [$administrador, $proyecto] = $this->projectFixture();
        Storage::disk('public')->put('documentos/prueba.pdf', '%PDF-1.4 archivo de prueba');
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'proyecto-prueba.pdf',
            'ruta_archivo' => 'documentos/prueba.pdf',
            'tipo_archivo' => 'pdf',
            'tamano_archivo' => 24,
        ]);

        $this->actingAs($administrador)
            ->get(route('proyectos.documento', $proyecto))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_missing_project_pdf_returns_not_found_instead_of_a_broken_link(): void
    {
        Storage::fake('public');
        [$administrador, $proyecto] = $this->projectFixture();
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'inexistente.pdf',
            'ruta_archivo' => 'documentos/inexistente.pdf',
            'tipo_archivo' => 'pdf',
        ]);

        $this->actingAs($administrador)
            ->get(route('proyectos.documento', $proyecto))
            ->assertNotFound();
    }

    public function test_authenticated_user_can_view_a_generated_report(): void
    {
        Storage::fake('public');
        [$administrador, $proyecto] = $this->projectFixture();
        Storage::disk('public')->put('reportes/reporte-prueba.pdf', '%PDF-1.4 reporte de prueba');
        $reporte = Reporte::create([
            'proyecto_id' => $proyecto->id,
            'generado_por' => $administrador->id,
            'ruta_pdf' => 'reportes/reporte-prueba.pdf',
        ]);

        $this->actingAs($administrador)
            ->get(route('reportes.archivo', $reporte))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_administrator_can_replace_a_missing_project_pdf(): void
    {
        Storage::fake('public');
        [$administrador, $proyecto] = $this->projectFixture();
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'perdido.pdf',
            'ruta_archivo' => 'documentos/perdido.pdf',
            'tipo_archivo' => 'pdf',
        ]);

        $response = $this->actingAs($administrador)->put(route('proyectos.update', $proyecto), [
            'titulo' => $proyecto->titulo,
            'resumen' => 'Resumen actualizado',
            'modalidad' => $proyecto->modalidad,
            'carrera_id' => $proyecto->carrera_id,
            'estudiante_id' => $proyecto->estudiante_id,
            'tutor_id' => null,
            'anio' => $proyecto->anio,
            'documento' => UploadedFile::fake()->create('repuesto.pdf', 20, 'application/pdf'),
        ]);

        $response->assertRedirect(route('proyectos.index'))->assertSessionHasNoErrors();
        $proyecto->refresh()->load('documento');
        Storage::disk('public')->assertExists($proyecto->documento->ruta_archivo);
        $this->assertSame('repuesto.pdf', $proyecto->documento->nombre_archivo);
        $this->assertSame('pendiente_analisis', $proyecto->estado);
    }

    public function test_administrator_can_archive_and_restore_a_project_without_deleting_it(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();

        $this->actingAs($administrador)
            ->patch(route('proyectos.archivo', $proyecto))
            ->assertRedirect(route('proyectos.index', ['vista' => 'archivados']));
        $this->assertDatabaseHas('proyectos_titulacion', ['id' => $proyecto->id, 'activo' => false]);

        $this->actingAs($administrador)
            ->patch(route('proyectos.archivo', $proyecto))
            ->assertRedirect(route('proyectos.index', ['vista' => 'activos']));
        $this->assertDatabaseHas('proyectos_titulacion', ['id' => $proyecto->id, 'activo' => true]);
    }

    public function test_teacher_cannot_archive_a_project(): void
    {
        [, $proyecto] = $this->projectFixture();
        $docente = User::factory()->create(['rol' => 'docente', 'activo' => true]);

        $this->actingAs($docente)
            ->patch(route('proyectos.archivo', $proyecto))
            ->assertForbidden();
        $this->assertTrue($proyecto->fresh()->activo);
    }

    public function test_project_search_only_returns_active_compatible_projects_with_text(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'principal.pdf',
            'ruta_archivo' => 'documentos/principal.pdf',
            'tipo_archivo' => 'pdf',
            'contenido_extraido' => 'Contenido de prueba disponible.',
        ]);

        $response = $this->actingAs($administrador)->getJson(route('analisis.proyectos.buscar', [
            'q' => 'Proyecto de prueba',
            'modalidad' => 'proyecto_grado',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $proyecto->id)
            ->assertJsonPath('data.0.modalidad', 'proyecto_grado');

        $this->actingAs($administrador)->getJson(route('analisis.proyectos.buscar', [
            'modalidad' => 'trabajo_dirigido_externo',
        ]))->assertJsonCount(0, 'data');

        $proyecto->update(['activo' => false]);
        $this->actingAs($administrador)->getJson(route('analisis.proyectos.buscar'))
            ->assertJsonCount(0, 'data');
    }

    public function test_student_only_sees_and_opens_reports_from_their_own_projects(): void
    {
        Storage::fake('public');
        [$administrador, $proyectoPropio] = $this->projectFixture();
        $estudiante = $proyectoPropio->estudiante;
        $otroEstudiante = User::factory()->create(['rol' => 'estudiante', 'activo' => true]);
        $otroProyecto = ProyectoTitulacion::create([
            'titulo' => 'Proyecto confidencial de otro estudiante',
            'modalidad' => 'proyecto_grado',
            'carrera_id' => $proyectoPropio->carrera_id,
            'estudiante_id' => $otroEstudiante->id,
            'anio' => 2026,
            'estado' => 'analizado',
            'activo' => true,
        ]);

        Storage::disk('public')->put('reportes/propio.pdf', '%PDF-1.4 propio');
        Storage::disk('public')->put('reportes/ajeno.pdf', '%PDF-1.4 ajeno');
        $reportePropio = Reporte::create(['proyecto_id' => $proyectoPropio->id, 'generado_por' => $administrador->id, 'ruta_pdf' => 'reportes/propio.pdf']);
        $reporteAjeno = Reporte::create(['proyecto_id' => $otroProyecto->id, 'generado_por' => $administrador->id, 'ruta_pdf' => 'reportes/ajeno.pdf']);

        $this->actingAs($estudiante)->get(route('reportes.index'))
            ->assertOk()
            ->assertSee($proyectoPropio->titulo)
            ->assertDontSee($otroProyecto->titulo);
        $this->actingAs($estudiante)->get(route('reportes.archivo', $reportePropio))->assertOk();
        $this->actingAs($estudiante)->get(route('reportes.archivo', $reporteAjeno))->assertForbidden();
    }

    private function projectFixture(): array
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $administrador = User::factory()->create(['rol' => 'administrador', 'activo' => true]);
        $estudiante = User::factory()->create(['rol' => 'estudiante', 'carrera_id' => $carrera->id, 'activo' => true]);
        $proyecto = ProyectoTitulacion::create([
            'titulo' => 'Proyecto de prueba',
            'modalidad' => 'proyecto_grado',
            'carrera_id' => $carrera->id,
            'estudiante_id' => $estudiante->id,
            'anio' => 2026,
            'estado' => 'pendiente_analisis',
            'activo' => true,
        ]);

        return [$administrador, $proyecto];
    }
}
