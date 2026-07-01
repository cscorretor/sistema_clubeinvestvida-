<?php

namespace Tests\Feature;

use App\Models\Profissao;
use App\Models\Usuario;
use Database\Seeders\ProfissaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfissaoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_exige_usuario_autenticado(): void
    {
        $this->getJson('/api/profissoes?q=med')
            ->assertUnauthorized();
    }

    public function test_nao_consulta_com_menos_de_tres_caracteres(): void
    {
        $usuario = Usuario::factory()->admin()->create();

        $this->actingAs($usuario)
            ->getJson('/api/profissoes?q=me')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_busca_titulo_sem_diferenciar_acentos_e_limita_resultados(): void
    {
        $usuario = Usuario::factory()->admin()->create();

        foreach (range(1, 12) as $indice) {
            Profissao::create([
                'codigo_cbo' => str_pad((string) $indice, 6, '0', STR_PAD_LEFT),
                'titulo' => "Médico de teste {$indice}",
                'titulo_busca' => "medico de teste {$indice}",
            ]);
        }

        $response = $this->actingAs($usuario)
            ->getJson('/api/profissoes?q=medico')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['codigo', 'titulo'],
                ],
            ]);

        $this->assertSame('Médico de teste 1', $response->json('data.0.titulo'));
    }

    public function test_busca_sinonimo_e_retorna_a_ocupacao_oficial(): void
    {
        $usuario = Usuario::factory()->admin()->create();
        $profissao = Profissao::create([
            'codigo_cbo' => '252105',
            'titulo' => 'Administrador',
            'titulo_busca' => 'administrador',
        ]);
        $profissao->sinonimos()->create([
            'titulo' => 'Administrador de empresas',
            'titulo_busca' => 'administrador de empresas',
        ]);

        $this->actingAs($usuario)
            ->getJson('/api/profissoes?q=empresas')
            ->assertOk()
            ->assertExactJson([
                'data' => [[
                    'codigo' => '252105',
                    'titulo' => 'Administrador',
                ]],
            ]);
    }

    public function test_seeder_importa_catalogo_oficial_e_sinonimos(): void
    {
        $this->seed(ProfissaoSeeder::class);

        $this->assertDatabaseHas('profissoes', [
            'codigo_cbo' => '010105',
            'titulo' => 'Oficial general da aeronáutica',
            'titulo_busca' => 'oficial general da aeronautica',
        ]);
        $this->assertDatabaseHas('profissao_sinonimos', [
            'titulo_busca' => 'brigadeiro',
        ]);
        $this->assertGreaterThan(2600, Profissao::query()->count());
    }
}
