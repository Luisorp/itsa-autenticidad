<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Documento;
use App\Models\Estudiante;
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

    public function test_manager_cannot_archive_a_project(): void
    {
        [, $proyecto] = $this->projectFixture();
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);

        $this->actingAs($gestor)
            ->patch(route('proyectos.archivo', $proyecto))
            ->assertForbidden();
        $this->assertTrue($proyecto->fresh()->activo);
    }

    public function test_manager_can_edit_projects_but_regular_user_cannot(): void
    {
        [, $proyecto] = $this->projectFixture();
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);

        $this->actingAs($gestor)
            ->get(route('proyectos.edit', $proyecto))
            ->assertOk();

        $this->actingAs($proyecto->estudiante->cuenta)
            ->get(route('proyectos.edit', $proyecto))
            ->assertForbidden();
    }

    public function test_each_project_shows_direct_compare_and_report_actions_according_to_permissions(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'acciones.pdf',
            'ruta_archivo' => 'documentos/acciones.pdf',
            'tipo_archivo' => 'pdf',
        ]);

        $compareUrl = route('analisis.index', ['proyecto_a' => $proyecto->id]);
        $reportUrl = route('proyectos.reporte', $proyecto);

        $this->actingAs($administrador)->get(route('proyectos.index'))
            ->assertOk()
            ->assertSee('Comparar')
            ->assertSee('Reporte')
            ->assertSee($compareUrl, false)
            ->assertSee($reportUrl, false);

        $this->actingAs($proyecto->estudiante->cuenta)->get(route('proyectos.index'))
            ->assertOk()
            ->assertDontSee($compareUrl, false)
            ->assertSee($reportUrl, false);
    }

    public function test_project_search_only_returns_active_compatible_projects_with_text(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'principal.pdf',
            'ruta_archivo' => 'documentos/principal.pdf',
            'tipo_archivo' => 'pdf',
            'contenido_extraido' => 'Contenido sin encabezados disponible para la búsqueda de proyectos y análisis académico.',
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

    public function test_project_catalog_can_filter_by_partial_title_or_student_name(): void
    {
        [$administrador, $proyecto] = $this->projectFixture();

        $this->actingAs($administrador)
            ->get(route('proyectos.index', ['buscar' => 'proyecto de pru']))
            ->assertOk()
            ->assertSee($proyecto->titulo);

        $this->actingAs($administrador)
            ->get(route('proyectos.index', ['buscar' => $proyecto->estudiante->nombre]))
            ->assertOk()
            ->assertSee($proyecto->titulo);
    }

    public function test_user_only_sees_and_opens_reports_from_their_own_projects(): void
    {
        Storage::fake('public');
        [$administrador, $proyectoPropio] = $this->projectFixture();
        $usuario = $proyectoPropio->estudiante->cuenta;
        $otroUsuario = User::factory()->create(['rol' => 'usuario', 'activo' => true]);
        $otroEstudiante = Estudiante::create([
            'nombre' => 'Otro estudiante',
            'carrera_id' => $proyectoPropio->carrera_id,
            'user_id' => $otroUsuario->id,
            'activo' => true,
        ]);
        $otroProyecto = ProyectoTitulacion::create([
            'titulo' => 'Proyecto confidencial de otro usuario',
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

        $this->actingAs($usuario)->get(route('reportes.index'))
            ->assertOk()
            ->assertSee($proyectoPropio->titulo)
            ->assertDontSee($otroProyecto->titulo);
        $this->actingAs($usuario)->get(route('reportes.archivo', $reportePropio))->assertOk();
        $this->actingAs($usuario)->get(route('reportes.archivo', $reporteAjeno))->assertForbidden();
    }

    public function test_full_text_is_used_for_manual_and_repository_comparisons(): void
    {
        [$admin, $project] = $this->projectFixture();
        $other = $project->replicate();
        $other->titulo = 'Otro proyecto';
        $other->save();
        $text = 'La plataforma permite registrar documentos académicos y comparar contenido mediante procesamiento del lenguaje natural.';
        $documents = [];
        foreach ([$project, $other] as $item) {
            $documents[] = Documento::create([
                'proyecto_id' => $item->id,
                'nombre_archivo' => 'prueba.pdf',
                'ruta_archivo' => 'documentos/prueba.pdf',
                'tipo_archivo' => 'pdf',
                'contenido_extraido' => $text,
            ]);
        }

        $this->actingAs($admin)->get(route('analisis.index', ['proyecto_a' => $project->id]))
            ->assertOk()->assertViewHas('proyectoA', fn ($selected) => $selected?->id === $project->id);
        $this->post(route('analisis.comparar'), ['proyecto_a' => $project->id, 'proyecto_b' => $other->id])
            ->assertOk()->assertViewHas('porcentaje', 100.0)
            ->assertViewHas('coincidencias', fn ($matches) => count($matches) > 0);
        $this->post(route('proyectos.analizar', $project))
            ->assertRedirect(route('proyectos.resultados', $project))->assertSessionMissing('error');
        $this->assertDatabaseHas('comparaciones', [
            'documento_a_id' => $documents[0]->id,
            'documento_b_id' => $documents[1]->id,
            'porcentaje_similitud' => 100,
        ]);

        $documents[0]->update(['contenido_extraido' => '   ']);
        $this->post(route('analisis.comparar'), ['proyecto_a' => $project->id, 'proyecto_b' => $other->id])
            ->assertSessionHasErrors('proyecto_a');
        $this->post(route('proyectos.analizar', $project))->assertSessionHas('error');
    }

    public function test_results_show_top_ten_and_allow_all_with_filters(): void
    {
        [$admin, $project] = $this->projectFixture();
        $document = Documento::create([
            'proyecto_id' => $project->id,
            'nombre_archivo' => 'prueba.pdf',
            'ruta_archivo' => 'documentos/prueba.pdf',
            'tipo_archivo' => 'pdf',
            'contenido_extraido' => 'Texto de prueba',
        ]);
        for ($score = 1; $score <= 12; $score++) {
            $other = $project->replicate();
            $other->titulo = 'Proyecto '.$score;
            $other->save();
            $otherDocument = $document->replicate();
            $otherDocument->proyecto_id = $other->id;
            $otherDocument->save();
            \App\Models\Comparacion::create([
                'documento_a_id' => $document->id,
                'documento_b_id' => $otherDocument->id,
                'porcentaje_similitud' => $score,
                'algoritmo_usado' => 'TF-IDF',
            ]);
        }

        $this->actingAs($admin)->get(route('proyectos.resultados', $project))
            ->assertOk()->assertSee('Ver todos')->assertViewHas('totalComparaciones', 12)
            ->assertViewHas('comparaciones', fn ($rows) => $rows->count() === 10
                && (float) $rows->first()->porcentaje_similitud === 12.0
                && (float) $rows->last()->porcentaje_similitud === 3.0);
        $this->get(route('proyectos.resultados', ['proyecto' => $project->id, 'todos' => 1]))
            ->assertOk()->assertSee('Ver solo los 10 más altos')
            ->assertViewHas('comparaciones', fn ($rows) => $rows->count() === 12);
        $this->get(route('proyectos.resultados', ['proyecto' => $project->id, 'todos' => 1, 'min' => 5, 'max' => 8]))
            ->assertOk()->assertViewHas('totalComparaciones', 4)
            ->assertViewHas('comparaciones', fn ($rows) => $rows->count() === 4
                && (float) $rows->first()->porcentaje_similitud === 8.0);
        $this->get(route('proyectos.resultados', ['proyecto' => $project->id, 'min' => 90]))
            ->assertOk()->assertViewHas('totalComparaciones', 0)->assertDontSee('Ver todos');
        $this->assertDatabaseCount('comparaciones', 12);
    }

    private function projectFixture(): array
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $administrador = User::factory()->create(['rol' => 'administrador', 'activo' => true]);
        $usuario = User::factory()->create(['rol' => 'usuario', 'carrera_id' => $carrera->id, 'activo' => true]);
        $estudiante = Estudiante::create([
            'nombre' => $usuario->name,
            'email' => $usuario->email,
            'carrera_id' => $carrera->id,
            'user_id' => $usuario->id,
            'activo' => true,
        ]);
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
