<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AcademicRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_register_students_and_teachers_without_creating_accounts(): void
    {
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);

        $this->actingAs($gestor)->get(route('estudiantes.index'))->assertOk()->assertSee('Padrón de estudiantes');
        $this->actingAs($gestor)->get(route('estudiantes.create'))->assertOk();
        $this->actingAs($gestor)->get(route('docentes.index'))->assertOk()->assertSee('Directorio docente');
        $this->actingAs($gestor)->get(route('docentes.create'))->assertOk();

        $this->actingAs($gestor)->post(route('estudiantes.store'), [
            'codigo' => 'EST-001',
            'nombre' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'carrera_id' => $carrera->id,
            'gestion_ingreso' => 2026,
        ])->assertRedirect(route('estudiantes.index'));

        $this->actingAs($gestor)->post(route('docentes.store'), [
            'codigo' => 'DOC-001',
            'nombre' => 'Carlos Flores',
            'email' => 'carlos@example.com',
            'especialidad' => 'Ingeniería de software',
            'carrera_ids' => [$carrera->id],
        ])->assertRedirect(route('docentes.index'));

        $this->assertDatabaseHas('estudiantes', ['codigo' => 'EST-001', 'user_id' => null]);
        $this->assertDatabaseHas('docentes', ['codigo' => 'DOC-001', 'user_id' => null]);
        $this->assertDatabaseHas('carrera_docente', ['carrera_id' => $carrera->id]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_manager_can_import_students_from_csv(): void
    {
        Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);
        $csv = "codigo,nombre,email,carrera,gestion\nEST-101,María López,maria@example.com,SIS,2026\nEST-102,Juan Quispe,,SIS,2025\n";

        $this->actingAs($gestor)->post(route('estudiantes.importar'), [
            'archivo' => UploadedFile::fake()->createWithContent('estudiantes.csv', $csv),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('estudiantes', ['codigo' => 'EST-101', 'nombre' => 'María López']);
        $this->assertDatabaseHas('estudiantes', ['codigo' => 'EST-102', 'gestion_ingreso' => 2025]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_regular_user_cannot_manage_academic_records(): void
    {
        $usuario = User::factory()->create(['rol' => 'usuario', 'activo' => true]);

        $this->actingAs($usuario)->get(route('estudiantes.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('docentes.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('analisis.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('crossref.index'))->assertForbidden();
    }

    public function test_project_searches_only_people_assigned_to_the_selected_career(): void
    {
        $sistemas = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS', 'activo' => true]);
        $contabilidad = Carrera::create(['nombre' => 'Contabilidad', 'codigo' => 'CON', 'activo' => true]);
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);

        $estudianteSistemas = Estudiante::create(['codigo' => 'SIS-01', 'nombre' => 'Estudiante Sistemas', 'carrera_id' => $sistemas->id]);
        Estudiante::create(['codigo' => 'CON-01', 'nombre' => 'Estudiante Contabilidad', 'carrera_id' => $contabilidad->id]);
        $docenteMulticarrera = Docente::create(['codigo' => 'DOC-01', 'nombre' => 'Tutor Multicarrera']);
        $docenteMulticarrera->carreras()->sync([$sistemas->id, $contabilidad->id]);
        $docenteContabilidad = Docente::create(['codigo' => 'DOC-02', 'nombre' => 'Tutor Contabilidad']);
        $docenteContabilidad->carreras()->sync([$contabilidad->id]);

        $this->actingAs($gestor)->getJson(route('proyectos.estudiantes.buscar', ['carrera_id' => $sistemas->id, 'q' => 'Estudiante']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $estudianteSistemas->id);

        $this->actingAs($gestor)->getJson(route('proyectos.docentes.buscar', ['carrera_id' => $sistemas->id, 'q' => 'Tutor']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $docenteMulticarrera->id);

        $this->actingAs($gestor)->from(route('proyectos.create'))->post(route('proyectos.store'), [
            'titulo' => 'Proyecto con tutor incompatible',
            'modalidad' => 'proyecto_grado',
            'carrera_id' => $sistemas->id,
            'estudiante_id' => $estudianteSistemas->id,
            'tutor_id' => $docenteContabilidad->id,
            'anio' => 2026,
            'documento' => UploadedFile::fake()->create('proyecto.pdf', 20, 'application/pdf'),
        ])->assertRedirect(route('proyectos.create'))->assertSessionHasErrors('tutor_id');
    }
}
