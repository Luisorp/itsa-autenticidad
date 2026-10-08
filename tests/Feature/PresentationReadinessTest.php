<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Docente;
use App\Models\Documento;
use App\Models\Estudiante;
use App\Models\ProyectoTitulacion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresentationReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        Storage::fake('public');
        config(['filesystems.backups_path' => storage_path('framework/testing/backups/'.bin2hex(random_bytes(6)))]);
        $admin = User::factory()->create(['rol' => 'administrador', 'activo' => true]);
        $gestor = User::factory()->create(['rol' => 'gestor', 'activo' => true]);
        $usuario = User::factory()->create(['rol' => 'usuario', 'activo' => true]);
        $carrera = Carrera::create(['nombre' => 'Sistemas', 'codigo' => 'SIS']);
        $estudiante = Estudiante::create(['nombre' => "Estudiante D'Angelo", 'carrera_id' => $carrera->id, 'user_id' => $usuario->id]);
        $docente = Docente::create(['nombre' => 'Docente de prueba', 'user_id' => $gestor->id]);
        $docente->carreras()->attach($carrera);
        $proyecto = ProyectoTitulacion::create([
            'titulo' => 'Proyecto de presentación', 'modalidad' => 'proyecto_grado',
            'carrera_id' => $carrera->id, 'estudiante_id' => $estudiante->id,
            'tutor_id' => $docente->id, 'anio' => 2026,
        ]);
        Documento::create([
            'proyecto_id' => $proyecto->id, 'nombre_archivo' => 'prueba.pdf',
            'ruta_archivo' => 'documentos/prueba.pdf',
            'contenido_extraido' => "INTRODUCCIÓN\nEsta propuesta implementa un sistema original para registrar documentos académicos y analizar su contenido.",
        ]);

        return compact('admin', 'gestor', 'usuario', 'carrera', 'estudiante', 'docente', 'proyecto');
    }

    public function test_main_pages_and_permissions_for_all_three_roles(): void
    {
        $e = $this->escenario();
        $comunes = ['/dashboard', '/proyectos', '/reportes', '/profile', '/proyectos/'.$e['proyecto']->id.'/resultados'];
        $gestion = ['/analizar', '/crossref', '/estudiantes', '/estudiantes/create', '/docentes', '/docentes/create', '/proyectos/create', '/proyectos/'.$e['proyecto']->id.'/edit'];
        $administracion = ['/carreras', '/carreras/create', '/usuarios', '/usuarios/create', '/respaldos'];
        foreach (['admin', 'gestor', 'usuario'] as $rol) {
            $this->actingAs($e[$rol]);
            foreach ($comunes as $url) { $this->get($url)->assertOk(); }
            foreach ($gestion as $url) {
                $response = $this->get($url);
                $rol === 'usuario' ? $response->assertForbidden() : $response->assertOk();
            }
            foreach ($administracion as $url) {
                $response = $this->get($url);
                $rol === 'admin' ? $response->assertOk() : $response->assertForbidden();
            }
        }
        $this->post(route('logout'))->assertRedirect('/');
        foreach (array_merge($comunes, $gestion, $administracion) as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_backup_download_and_restore_in_a_separate_sqlite_database(): void
    {
        $e = $this->escenario();
        $this->actingAs($e['admin'])->post(route('respaldos.generar'))->assertRedirect(route('respaldos.index'));
        $archivos = File::files(config('filesystems.backups_path'));
        $this->assertCount(1, $archivos);
        $sql = File::get($archivos[0]->getPathname());
        $this->get(route('respaldos.descargar', $archivos[0]->getFilename()))->assertOk();

        $restore = new \PDO('sqlite::memory:');
        foreach (DB::select("SELECT sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'") as $tabla) {
            $restore->exec($tabla->sql);
        }
        $restore->exec($sql);
        foreach (['carreras', 'users', 'estudiantes', 'docentes', 'carrera_docente', 'proyectos_titulacion', 'documentos', 'comparaciones', 'reportes'] as $tabla) {
            $this->assertSame(DB::table($tabla)->count(), (int) $restore->query('SELECT COUNT(*) FROM '.$tabla)->fetchColumn());
        }
        $this->assertSame("Estudiante D'Angelo", $restore->query('SELECT nombre FROM estudiantes')->fetchColumn());
        $this->assertSame([], $restore->query('PRAGMA foreign_key_check')->fetchAll());
    }

    public function test_upload_of_a_real_pdf_and_reports_with_identical_titles(): void
    {
        $e = $this->escenario();
        $pdf = Pdf::loadHTML('<h1>INTRODUCCIÓN</h1><p>Esta propuesta automatiza la recepción de documentos académicos y permite validar cada entrega mediante un registro específico para estudiantes.</p>')->output();
        $this->actingAs($e['admin'])->post(route('proyectos.store'), [
            'titulo' => $e['proyecto']->titulo, 'modalidad' => 'proyecto_grado',
            'carrera_id' => $e['carrera']->id, 'estudiante_id' => $e['estudiante']->id,
            'tutor_id' => $e['docente']->id, 'anio' => 2026,
            'documento' => UploadedFile::fake()->createWithContent('real.pdf', $pdf),
        ])->assertRedirect(route('proyectos.index'))->assertSessionHasNoErrors();
        $nuevo = ProyectoTitulacion::latest('id')->firstOrFail();
        $this->assertStringContainsString('automatiza', $nuevo->documento->contenido_extraido);
        $this->get(route('proyectos.documento', $nuevo))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->post(route('proyectos.analizar', $nuevo))->assertRedirect(route('proyectos.resultados', $nuevo));
        foreach ([$e['proyecto'], $nuevo] as $proyecto) {
            $this->get(route('proyectos.reporte', $proyecto))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
        $this->assertNotSame($e['proyecto']->reportes()->firstOrFail()->ruta_pdf, $nuevo->reportes()->firstOrFail()->ruta_pdf);
        foreach ([$e['proyecto'], $nuevo] as $proyecto) { Storage::disk('public')->assertExists($proyecto->reportes()->firstOrFail()->ruta_pdf); }
    }

    public function test_career_and_account_management_preserves_historical_records(): void
    {
        $e = $this->escenario();
        $this->actingAs($e['admin'])->post(route('carreras.store'), ['nombre' => 'Carrera nueva', 'codigo' => 'NUE'])->assertSessionHasNoErrors()->assertRedirect(route('carreras.index'));
        $carrera = Carrera::where('codigo', 'NUE')->firstOrFail();
        $this->put(route('carreras.update', $carrera), ['nombre' => 'Carrera actualizada', 'codigo' => 'NUE'])->assertSessionHasNoErrors();
        $this->patch(route('carreras.toggle-activo', $carrera))->assertRedirect();
        $this->assertFalse($carrera->fresh()->activo);
        $this->post(route('usuarios.store'), ['name' => 'Cuenta nueva', 'email' => 'cuenta-qa@example.com', 'rol' => 'gestor', 'password' => 'Prueba-QA2026!', 'password_confirmation' => 'Prueba-QA2026!'])->assertSessionHasNoErrors()->assertRedirect();
        $user = User::where('email', 'cuenta-qa@example.com')->firstOrFail();
        $this->put(route('usuarios.update', $user), ['name' => 'Cuenta actualizada', 'email' => $user->email, 'rol' => 'gestor'])->assertSessionHasNoErrors();
        $this->patch(route('usuarios.toggle-activo', $user))->assertRedirect();
        $this->assertFalse((bool) $user->fresh()->activo);
        $this->patch(route('usuarios.toggle-activo', $e['admin']))->assertSessionHas('error');
        $this->assertDatabaseHas('proyectos_titulacion', ['id' => $e['proyecto']->id]);
    }
}
