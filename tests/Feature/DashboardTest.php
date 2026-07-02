<?php

namespace Tests\Feature;

use App\Models\Apolice;
use App\Models\Cliente;
use App\Models\Ramo;
use App\Models\Seguradora;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exibe_metricas_grafico_renovacoes_e_aniversarios_reais(): void
    {
        $this->travelTo(Carbon::parse('2026-07-02 10:00:00'));

        $admin = Usuario::factory()->admin()->create();
        $ramo = Ramo::create(['nome' => 'Vida', 'grupo' => 'PESSOAS']);
        $seguradora = Seguradora::create(['nome' => 'Seguradora Painel', 'ativo' => true]);
        $cliente = Cliente::create([
            'codigo' => 'CLI-PAINEL',
            'pessoa' => 'PF',
            'tipo_cliente' => 'EFETIVO',
            'status' => 'ATIVO',
            'nome' => 'Ana Aniversariante',
            'cpf_cnpj' => '52998224725',
            'nascimento' => '1990-07-02',
            'data_cadastro' => today(),
        ]);
        $apolice = Apolice::create([
            'cliente_id' => $cliente->id,
            'ramo_id' => $ramo->id,
            'seguradora_id' => $seguradora->id,
            'num_apolice' => 'AP-PAINEL',
            'status' => 'ATIVO',
            'fim_vigencia' => today()->addDays(20),
        ]);
        $apolice->parcelas()->create([
            'numero' => 1,
            'vencimento' => today()->addDays(10),
            'valor_cliente' => 300,
            'valor_comissao' => 45.50,
            'status' => 'ABERTO',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Clientes ativos')
            ->assertSee('Apólices ativas')
            ->assertSee('Produção dos últimos 6 meses')
            ->assertSee('Carteira por produto')
            ->assertSee('Seguradora Painel')
            ->assertSee('Ana Aniversariante')
            ->assertSee('Completa 36 anos')
            ->assertSee('Hoje')
            ->assertSee('R$ 45,50');
    }
}
