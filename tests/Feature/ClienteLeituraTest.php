<?php

namespace Tests\Feature;

use App\Models\Apolice;
use App\Models\Chamado;
use App\Models\Cliente;
use App\Models\Produtor;
use App\Models\Ramo;
use App\Models\Seguradora;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteLeituraTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_clientes_ativos_com_paginacao_real(): void
    {
        $admin = Usuario::factory()->admin()->create();

        foreach (range(1, 12) as $index) {
            $this->createCliente(sprintf('Cliente %02d', $index), status: 'ATIVO');
        }
        $this->createCliente('Cliente Inativo', status: 'INATIVO');

        $response = $this->actingAs($admin)->get('/clientes');

        $response->assertOk()
            ->assertSee('Cliente 01')
            ->assertDontSee('Cliente Inativo')
            ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 12
                && $clientes->count() === 10
                && $clientes->perPage() === 10);

        $this->actingAs($admin)
            ->get('/clientes?page=2')
            ->assertOk()
            ->assertViewHas('clientes', fn ($clientes) => $clientes->count() === 2);
    }

    public function test_busca_por_nome_cpf_e_telefone(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $maria = $this->createCliente('Maria Encontrada', cpf: '52998224725');
        $maria->telefones()->create([
            'padrao' => true,
            'tipo' => 'WHATSAPP',
            'numero' => '(11) 99999-8888',
        ]);
        $this->createCliente('Outro Cliente', cpf: '11144477735');

        $this->actingAs($admin)
            ->get('/clientes?status=TODOS&busca=529.982.247-25')
            ->assertOk()
            ->assertSee('Maria Encontrada')
            ->assertDontSee('Outro Cliente');

        $this->actingAs($admin)
            ->get('/clientes?status=TODOS&busca=11999998888')
            ->assertOk()
            ->assertSee('Maria Encontrada')
            ->assertDontSee('Outro Cliente');
    }

    public function test_filtros_por_tipo_cidade_e_ramo(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $vida = Ramo::create(['nome' => 'Vida', 'grupo' => 'PESSOAS']);
        $saude = Ramo::create(['nome' => 'Saúde', 'grupo' => 'PESSOAS']);

        $clienteVida = $this->createCliente('Cliente Vida', tipo: 'EFETIVO');
        $clienteVida->enderecos()->create([
            'padrao' => true,
            'tipo' => 'RESIDENCIAL',
            'cidade' => 'Blumenau',
            'uf' => 'SC',
        ]);
        Apolice::create([
            'cliente_id' => $clienteVida->id,
            'ramo_id' => $vida->id,
            'status' => 'ATIVO',
        ]);

        $clienteSaude = $this->createCliente('Cliente Saúde', tipo: 'PROSPECT');
        $clienteSaude->enderecos()->create([
            'padrao' => true,
            'tipo' => 'RESIDENCIAL',
            'cidade' => 'Joinville',
            'uf' => 'SC',
        ]);
        Apolice::create([
            'cliente_id' => $clienteSaude->id,
            'ramo_id' => $saude->id,
            'status' => 'ATIVO',
        ]);

        $this->actingAs($admin)
            ->get("/clientes?tipo=EFETIVO&status=TODOS&cidade=Blumenau&ramo={$vida->id}")
            ->assertOk()
            ->assertSee('Cliente Vida')
            ->assertDontSee('Cliente Saúde');
    }

    public function test_ficha_exibe_dados_reais_e_mascara_cpf(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $ramo = Ramo::create(['nome' => 'Vida', 'grupo' => 'PESSOAS']);
        $seguradora = Seguradora::create(['nome' => 'Seguradora Teste', 'ativo' => true]);
        $cliente = $this->createCliente('Maria Detalhada', cpf: '52998224725');
        $cliente->telefones()->create([
            'padrao' => true,
            'tipo' => 'WHATSAPP',
            'numero' => '(11) 99999-8888',
        ]);
        $cliente->emails()->create([
            'padrao' => true,
            'email' => 'maria.detalhada@example.com',
        ]);
        $cliente->enderecos()->create([
            'padrao' => true,
            'tipo' => 'RESIDENCIAL',
            'logradouro' => 'Rua das Flores',
            'numero' => '10',
            'cidade' => 'Blumenau',
            'uf' => 'SC',
        ]);
        $apolice = Apolice::create([
            'cliente_id' => $cliente->id,
            'ramo_id' => $ramo->id,
            'seguradora_id' => $seguradora->id,
            'num_apolice' => 'AP-12345',
            'status' => 'ATIVO',
            'fim_vigencia' => now()->addMonths(6)->toDateString(),
        ]);
        $apolice->parcelas()->create([
            'numero' => 1,
            'vencimento' => now()->addMonth()->toDateString(),
            'valor_cliente' => 280,
            'status' => 'ABERTO',
        ]);
        Chamado::create([
            'cliente_id' => $cliente->id,
            'tipo' => 'CLIENTE',
            'descricao' => 'Atualizar beneficiários',
            'status' => 'PENDENTE',
            'prioridade' => 'MEDIA',
        ]);

        $this->actingAs($admin)
            ->get("/clientes/{$cliente->id}")
            ->assertOk()
            ->assertSee('Maria Detalhada')
            ->assertSee('529.***.***-25')
            ->assertDontSee('52998224725')
            ->assertSee('(11) 99999-8888')
            ->assertSee('maria.detalhada@example.com')
            ->assertSee('AP-12345')
            ->assertSee('Seguradora Teste')
            ->assertSee('Atualizar beneficiários')
            ->assertSee('Rua das Flores');
    }

    public function test_produtor_so_lista_e_abre_clientes_da_propria_carteira(): void
    {
        $produtor = Produtor::create(['nome' => 'Produtor A', 'ativo' => true]);
        $outroProdutor = Produtor::create(['nome' => 'Produtor B', 'ativo' => true]);
        $usuario = Usuario::factory()->produtor($produtor->id)->create();
        $clienteProprio = $this->createCliente('Cliente Próprio', produtorId: $produtor->id);
        $clienteAlheio = $this->createCliente('Cliente Alheio', produtorId: $outroProdutor->id);

        $this->actingAs($usuario)
            ->get('/clientes?status=TODOS')
            ->assertOk()
            ->assertSee('Cliente Próprio')
            ->assertDontSee('Cliente Alheio');

        $this->actingAs($usuario)->get("/clientes/{$clienteProprio->id}")->assertOk();
        $this->actingAs($usuario)->get("/clientes/{$clienteAlheio->id}")->assertNotFound();
    }

    private function createCliente(
        string $nome,
        string $cpf = '52998224725',
        string $status = 'ATIVO',
        string $tipo = 'PROSPECT',
        ?int $produtorId = null,
    ): Cliente {
        static $sequence = 0;
        $sequence++;

        return Cliente::create([
            'codigo' => sprintf('CLI-T%04d', $sequence),
            'pessoa' => 'PF',
            'tipo_cliente' => $tipo,
            'status' => $status,
            'produtor_id' => $produtorId,
            'nome' => $nome,
            'cpf_cnpj' => $cpf,
            'data_cadastro' => now()->toDateString(),
        ]);
    }
}
