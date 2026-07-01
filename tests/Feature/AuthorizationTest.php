<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Permissao;
use App\Models\Produtor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rota_administrativa_aceita_admin_e_rejeita_outros_perfis(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $comum = Usuario::factory()->create();

        $this->actingAs($admin)->get('/acesso-administrativo')->assertNoContent();
        $this->actingAs($comum)->get('/acesso-administrativo')->assertForbidden();
    }

    public function test_usuario_comum_depende_da_permissao_do_modulo(): void
    {
        $usuario = Usuario::factory()->create();

        $this->actingAs($usuario)->get('/controle-clientes')->assertForbidden();

        Permissao::create([
            'usuario_id' => $usuario->id,
            'modulo' => 'clientes',
            'pode_ver' => true,
            'pode_editar' => false,
        ]);

        $this->actingAs($usuario)->get('/controle-clientes')->assertNoContent();
    }

    public function test_produtor_enxerga_somente_clientes_da_propria_carteira(): void
    {
        $produtor = Produtor::create(['nome' => 'Produtor A', 'ativo' => true]);
        $outroProdutor = Produtor::create(['nome' => 'Produtor B', 'ativo' => true]);
        $usuario = Usuario::factory()->produtor($produtor->id)->create();

        $clienteVisivel = Cliente::create([
            'nome' => 'Cliente da carteira',
            'produtor_id' => $produtor->id,
        ]);
        $clienteDeOutroProdutor = Cliente::create([
            'nome' => 'Cliente de outro produtor',
            'produtor_id' => $outroProdutor->id,
        ]);

        $clientes = Cliente::query()->visivelPara($usuario)->get();

        $this->assertCount(1, $clientes);
        $this->assertTrue($clientes->first()->is($clienteVisivel));
        $this->assertTrue($usuario->can('view', $clienteVisivel));
        $this->assertFalse($usuario->can('view', $clienteDeOutroProdutor));
    }
}
