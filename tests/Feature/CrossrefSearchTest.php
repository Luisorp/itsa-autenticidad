<?php

namespace Tests\Feature;

use App\Models\Carrera;
use App\Models\Documento;
use App\Models\Estudiante;
use App\Models\ProyectoTitulacion;
use App\Models\User;
use App\Services\CrossrefService;
use App\Services\OpenAlexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrossrefSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_consulta_requiere_autenticacion(): void
    {
        $this->get('/crossref')->assertRedirect('/login');
    }

    public function test_busca_una_publicacion_por_titulo(): void
    {
        Cache::flush();
        Http::fake([
            'api.crossref.org/works*' => Http::response([
                'message' => ['items' => [[
                    'DOI' => '10.1234/prueba',
                    'title' => ['Una publicación de prueba'],
                    'author' => [['given' => 'Ana', 'family' => 'Pérez']],
                    'published' => ['date-parts' => [[2026, 3, 2]]],
                    'container-title' => ['Revista Académica'],
                    'type' => 'journal-article',
                ]]],
            ]),
        ]);

        $resultados = app(CrossrefService::class)->buscar('titulo', 'publicacion');

        $this->assertSame('Una publicación de prueba', $resultados[0]['titulo']);
        $this->assertSame(['Ana Pérez'], $resultados[0]['autores']);
        $this->assertSame('10.1234/prueba', $resultados[0]['doi']);

        Http::assertSent(fn ($peticion) => $peticion->url() === 'https://api.crossref.org/works?query.bibliographic=publicacion&rows=10&select=DOI%2Ctitle%2Cauthor%2Cpublished%2Ccontainer-title%2Ctype%2CURL%2Cabstract'
        );
    }

    public function test_la_busqueda_muestra_el_selector_y_exige_un_proyecto(): void
    {
        $gestor = User::factory()->create(['rol' => 'gestor']);

        $this->actingAs($gestor)
            ->get(route('crossref.index'))
            ->assertOk()
            ->assertSee('Proyecto que se comparará')
            ->assertSee('Crossref')
            ->assertSee('OpenAlex');

        $this->actingAs($gestor)
            ->from(route('crossref.index'))
            ->post(route('crossref.comparar'))
            ->assertRedirect(route('crossref.index'))
            ->assertSessionHasErrors('proyecto_id');
    }

    public function test_busca_y_normaliza_una_publicacion_en_openalex(): void
    {
        Cache::flush();
        Http::fake([
            'api.openalex.org/works*' => Http::response([
                'results' => [[
                    'id' => 'https://openalex.org/W123',
                    'doi' => 'https://doi.org/10.5555/openalex.prueba',
                    'title' => 'Investigación registrada en OpenAlex',
                    'authorships' => [['author' => ['display_name' => 'María Quispe']]],
                    'primary_location' => ['source' => ['display_name' => 'Revista Tecnológica']],
                    'publication_date' => '2026-08-10',
                    'type' => 'article',
                    'cited_by_count' => 14,
                    'open_access' => ['is_oa' => true],
                    'abstract_inverted_index' => ['Resumen' => [0], 'académico' => [1]],
                ]],
            ]),
        ]);

        $resultados = app(OpenAlexService::class)->buscar('titulo', 'investigacion');

        $this->assertSame('Investigación registrada en OpenAlex', $resultados[0]['titulo']);
        $this->assertSame(['María Quispe'], $resultados[0]['autores']);
        $this->assertSame(14, $resultados[0]['citas']);
        $this->assertTrue($resultados[0]['acceso_abierto']);

        Http::assertSent(function ($peticion) {
            parse_str((string) parse_url($peticion->url(), PHP_URL_QUERY), $consulta);

            return str_starts_with($peticion->url(), 'https://api.openalex.org/works?')
                && ($consulta['search'] ?? null) === 'investigacion'
                && ($consulta['per_page'] ?? null) === '10';
        });
    }

    public function test_compara_un_proyecto_con_resultados_de_ambas_fuentes(): void
    {
        Cache::flush();
        Http::fake([
            'api.crossref.org/works*' => Http::response(['message' => ['items' => [[
                'DOI' => '10.1234/externo',
                'title' => ['Sistema inteligente para mejorar procesos educativos'],
                'author' => [['given' => 'Ana', 'family' => 'Pérez']],
                'abstract' => '<jats:p>El sistema inteligente permite mejorar los procesos educativos mediante tecnología adaptativa moderna.</jats:p>',
                'published' => ['date-parts' => [[2026]]],
                'container-title' => ['Revista Educativa'],
                'type' => 'journal-article',
            ]]]]),
            'api.openalex.org/works*' => Http::response(['results' => [[
                'id' => 'https://openalex.org/W999',
                'doi' => null,
                'title' => 'Tecnología adaptativa en educación',
                'authorships' => [],
                'primary_location' => null,
                'publication_date' => '2025-01-01',
                'type' => 'article',
                'cited_by_count' => 2,
                'open_access' => ['is_oa' => true],
                'abstract_inverted_index' => ['tecnología' => [0], 'adaptativa' => [1], 'para' => [2], 'procesos' => [3], 'educativos' => [4]],
            ]]]),
        ]);

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
            'titulo' => 'Sistema inteligente para procesos educativos',
            'modalidad' => 'proyecto_grado',
            'carrera_id' => $carrera->id,
            'estudiante_id' => $estudiante->id,
            'anio' => 2026,
            'estado' => 'pendiente_analisis',
            'activo' => true,
        ]);
        Documento::create([
            'proyecto_id' => $proyecto->id,
            'nombre_archivo' => 'proyecto.pdf',
            'ruta_archivo' => 'documentos/proyecto.pdf',
            'tipo_archivo' => 'pdf',
            'contenido_extraido' => 'El sistema inteligente permite mejorar los procesos educativos mediante tecnología adaptativa moderna.',
        ]);

        $this->actingAs($administrador)
            ->get(route('crossref.index', ['proyecto_id' => $proyecto->id]))
            ->assertOk()
            ->assertSee($proyecto->titulo);

        $this->actingAs($administrador)
            ->post(route('crossref.comparar'), ['proyecto_id' => $proyecto->id])
            ->assertOk()
            ->assertSee('Sistema inteligente para mejorar procesos educativos')
            ->assertSee('Tecnología adaptativa en educación')
            ->assertSee('Crossref')
            ->assertSee('OpenAlex');

        $this->actingAs($administrador)
            ->get(route('proyectos.analisis-externo', $proyecto))
            ->assertRedirect(route('crossref.index', ['proyecto_id' => $proyecto->id]));
    }
}
