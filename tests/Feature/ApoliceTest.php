<?php

namespace Tests\Feature;

use App\Models\Apolice;
use App\Models\Cliente;
use App\Models\Produtor;
use App\Models\Ramo;
use App\Models\Seguradora;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApoliceTest extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_exibe_produtos_e_campos_funcionais(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        $this->catalogo();

        $this->actingAs($admin)
            ->get("/clientes/{$cliente->id}/apolices/nova")
            ->assertOk()
            ->assertSee('Nova proposta/apólice')
            ->assertSee('Vida')
            ->assertSee('Previdência')
            ->assertSee('Saúde')
            ->assertSee('Residencial')
            ->assertSee('Vidas Seguradas')
            ->assertSee('Dados da Previdência')
            ->assertSee('Dados do Plano de Saúde')
            ->assertSee('Dados do Imóvel Segurado')
            ->assertSee('Salvar proposta/apólice');
    }

    public function test_cadastra_vida_previdencia_saude_e_residencial_no_backend(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        [$ramos, $seguradora] = $this->catalogo();

        foreach (['Vida', 'Previdência', 'Saúde', 'Residencial'] as $index => $produto) {
            $payload = $this->payload($ramos[$produto], $seguradora, $produto, $index + 1);

            $this->actingAs($admin)
                ->post("/clientes/{$cliente->id}/apolices", $payload)
                ->assertRedirect("/clientes/{$cliente->id}")
                ->assertSessionHas('status');

            $apolice = Apolice::query()->where('num_proposta', 'PROP-QA-'.($index + 1))->firstOrFail();
            $this->assertSame($produto, $apolice->ramo->nome);
            $this->assertCount(1, $apolice->coberturas);
            $this->assertCount(12, $apolice->parcelas);

            if (in_array($produto, ['Vida', 'Saúde'], true)) {
                $this->assertCount(1, $apolice->vidas);
            }
            if ($produto === 'Previdência') {
                $this->assertSame('VGBL', $apolice->dados_produto['modalidade_previdencia']);
                $this->assertSame('REGRESSIVO', $apolice->dados_produto['regime_tributario']);
            }
            if ($produto === 'Saúde') {
                $this->assertSame('APARTAMENTO', $apolice->dados_produto['acomodacao']);
                $this->assertSame('NACIONAL', $apolice->dados_produto['abrangencia']);
            }
            if ($produto === 'Residencial') {
                $this->assertSame('CASA', $apolice->dados_produto['tipo_imovel']);
                $this->assertSame('SC', $apolice->dados_produto['uf_imovel']);
            }
        }

        $this->assertDatabaseCount('apolices', 4);
        $this->assertDatabaseCount('apolice_parcelas', 48);
        $this->assertDatabaseCount('audit_log', 4);

        $this->actingAs($admin)
            ->get('/apolices')
            ->assertOk()
            ->assertSee('Propostas e apólices')
            ->assertSee('Vida')
            ->assertSee('Previdência')
            ->assertSee('Saúde')
            ->assertSee('Residencial')
            ->assertSee('PROP-QA-1');

        $this->actingAs($admin)
            ->get("/clientes/{$cliente->id}")
            ->assertOk()
            ->assertSee('PROP-QA-1')
            ->assertSee('Editar')
            ->assertSee('+ Nova proposta/apólice');

        $auditJson = DB::table('audit_log')->pluck('dados_depois')->implode(' ');
        $this->assertStringNotContainsString('52998224725', $auditJson);
    }

    public function test_rejeita_beneficiarios_que_nao_somam_cem_porcento(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        [$ramos, $seguradora] = $this->catalogo();
        $payload = $this->payload($ramos['Vida'], $seguradora, 'Vida', 1);
        $payload['beneficiarios'][0]['percentual'] = 80;

        $this->actingAs($admin)
            ->from("/clientes/{$cliente->id}/apolices/nova")
            ->post("/clientes/{$cliente->id}/apolices", $payload)
            ->assertRedirect("/clientes/{$cliente->id}/apolices/nova")
            ->assertSessionHasErrors('beneficiarios');

        $this->assertDatabaseCount('apolices', 0);
    }

    public function test_converte_proposta_em_apolice_ativa_e_registra_auditoria(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        [$ramos, $seguradora] = $this->catalogo();
        $payload = $this->payload($ramos['Vida'], $seguradora, 'Vida', 1);

        $this->actingAs($admin)->post("/clientes/{$cliente->id}/apolices", $payload);
        $apolice = Apolice::firstOrFail();
        $payload['num_apolice'] = 'AP-QA-001';
        $payload['status'] = 'ATIVO';

        $this->actingAs($admin)
            ->put("/apolices/{$apolice->id}", $payload)
            ->assertRedirect("/clientes/{$cliente->id}")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('apolices', [
            'id' => $apolice->id,
            'num_proposta' => 'PROP-QA-1',
            'num_apolice' => 'AP-QA-001',
            'status' => 'ATIVO',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'entidade' => 'apolices',
            'entidade_id' => $apolice->id,
            'acao' => 'ALTERAR',
        ]);
        $this->assertDatabaseCount('apolice_parcelas', 12);
    }

    public function test_status_ativo_exige_numero_e_vigencia_da_apolice(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        [$ramos, $seguradora] = $this->catalogo();
        $payload = $this->payload($ramos['Vida'], $seguradora, 'Vida', 1);
        $payload['status'] = 'ATIVO';
        $payload['inicio_vigencia'] = null;
        $payload['fim_vigencia'] = null;

        $this->actingAs($admin)
            ->post("/clientes/{$cliente->id}/apolices", $payload)
            ->assertSessionHasErrors(['num_apolice', 'inicio_vigencia', 'fim_vigencia']);
    }

    public function test_produtor_nao_acessa_apolices_de_cliente_de_outra_carteira(): void
    {
        $produtor = Produtor::create(['nome' => 'Produtor A', 'ativo' => true]);
        $outro = Produtor::create(['nome' => 'Produtor B', 'ativo' => true]);
        $usuario = Usuario::factory()->produtor($produtor->id)->create();
        $clienteAlheio = $this->cliente($outro->id);
        $this->catalogo();

        $this->actingAs($usuario)
            ->get("/clientes/{$clienteAlheio->id}/apolices/nova")
            ->assertNotFound();
    }

    public function test_busca_e_filtros_retornam_apolices_correspondentes(): void
    {
        $admin = Usuario::factory()->admin()->create();
        [$ramos, $seguradora] = $this->catalogo();
        $carlos = $this->cliente();
        $carlos->update(['nome' => 'Carlos da Carteira']);
        $mariana = Cliente::create([
            'codigo' => 'CLI-MARIANA',
            'pessoa' => 'PF',
            'tipo_cliente' => 'EFETIVO',
            'status' => 'ATIVO',
            'nome' => 'Mariana Saúde',
            'cpf_cnpj' => '11144477735',
        ]);
        Apolice::create([
            'cliente_id' => $carlos->id,
            'ramo_id' => $ramos['Vida']->id,
            'seguradora_id' => $seguradora->id,
            'num_proposta' => 'PROP-CAR-001',
            'status' => 'ATIVO',
        ]);
        Apolice::create([
            'cliente_id' => $mariana->id,
            'ramo_id' => $ramos['Saúde']->id,
            'seguradora_id' => $seguradora->id,
            'num_proposta' => 'PROP-MAR-001',
            'status' => 'EM_EMISSAO',
        ]);

        $this->actingAs($admin)
            ->get('/apolices?busca=CAR')
            ->assertOk()
            ->assertSee('Carlos da Carteira')
            ->assertDontSee('Mariana Saúde');

        $this->actingAs($admin)
            ->get("/apolices?ramo={$ramos['Saúde']->id}&status=EM_EMISSAO")
            ->assertOk()
            ->assertSee('Mariana Saúde')
            ->assertDontSee('Carlos da Carteira');
    }

    public function test_busca_cliente_sem_apolice_oferece_criar_proposta(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $cliente = $this->cliente();
        $cliente->update(['nome' => 'Carlos Sem Apólice']);
        $this->catalogo();

        $this->actingAs($admin)
            ->get('/apolices?busca=CAR')
            ->assertOk()
            ->assertSee('Clientes encontrados')
            ->assertSee('Carlos Sem Apólice')
            ->assertSee('Criar proposta')
            ->assertSee(route('apolices.create', $cliente), false);
    }

    /**
     * @return array{array<string, Ramo>, Seguradora}
     */
    private function catalogo(): array
    {
        $ramos = [];
        foreach (['Vida' => 'PESSOAS', 'Previdência' => 'PESSOAS', 'Saúde' => 'PESSOAS', 'Residencial' => 'PATRIMONIAL'] as $nome => $grupo) {
            $ramos[$nome] = Ramo::create(['nome' => $nome, 'grupo' => $grupo]);
        }

        return [$ramos, Seguradora::create(['nome' => 'Seguradora QA', 'ativo' => true])];
    }

    private function cliente(?int $produtorId = null): Cliente
    {
        return Cliente::create([
            'codigo' => 'CLI-QA-'.fake()->unique()->numerify('#####'),
            'pessoa' => 'PF',
            'tipo_cliente' => 'PROSPECT',
            'status' => 'ATIVO',
            'produtor_id' => $produtorId,
            'nome' => 'Cliente Seguro QA',
            'cpf_cnpj' => '52998224725',
            'nascimento' => '1988-04-15',
            'data_cadastro' => now()->toDateString(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Ramo $ramo, Seguradora $seguradora, string $produto, int $sequence): array
    {
        $payload = [
            'ramo_id' => $ramo->id,
            'seguradora_id' => $seguradora->id,
            'tipo_proposta' => 'NOVO',
            'num_proposta' => "PROP-QA-{$sequence}",
            'num_apolice' => null,
            'status' => 'EM_EMISSAO',
            'inicio_vigencia' => '2026-08-01',
            'fim_vigencia' => '2027-07-31',
            'capital_segurado' => '150000.00',
            'coberturas' => [Apolice::COBERTURAS_POR_PRODUTO[$produto][0]],
            'valor_mensal' => '250.50',
            'primeiro_vencimento' => '2026-08-10',
            'vidas' => [],
            'beneficiarios' => [],
            'dados_produto' => [],
        ];

        if (in_array($produto, ['Vida', 'Saúde'], true)) {
            $payload['vidas'] = [[
                'nome' => 'Cliente Seguro QA',
                'parentesco' => 'TITULAR',
                'nascimento' => '1988-04-15',
                'capital' => '150000.00',
            ]];
        }

        if ($produto === 'Vida') {
            $payload['beneficiarios'] = [[
                'nome' => 'Beneficiário QA',
                'parentesco' => 'Cônjuge',
                'percentual' => 100,
            ]];
        }

        if ($produto === 'Previdência') {
            $payload['dados_produto'] = [
                'modalidade_previdencia' => 'VGBL',
                'regime_tributario' => 'REGRESSIVO',
            ];
        }

        if ($produto === 'Saúde') {
            $payload['dados_produto'] = [
                'acomodacao' => 'APARTAMENTO',
                'abrangencia' => 'NACIONAL',
                'coparticipacao' => true,
            ];
        }

        if ($produto === 'Residencial') {
            $payload['dados_produto'] = [
                'tipo_imovel' => 'CASA',
                'cep_imovel' => '89010-000',
                'endereco_imovel' => 'Rua das Flores, 100',
                'cidade_imovel' => 'Blumenau',
                'uf_imovel' => 'sc',
            ];
        }

        return $payload;
    }
}
