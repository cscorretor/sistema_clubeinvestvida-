<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Produtor;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClienteCadastroTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_abrir_o_formulario_de_cadastro(): void
    {
        $admin = Usuario::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/clientes/novo')
            ->assertOk()
            ->assertSee('Cadastro de Cliente')
            ->assertSee('Finalizar cadastro')
            ->assertSee('Canal de origem do cliente')
            ->assertSee('Google Ads')
            ->assertSee('phone-number', false)
            ->assertSee('inputmode="tel"', false)
            ->assertSee('aria-label="Número do telefone"', false)
            ->assertSee('mobile-nav-toggle', false)
            ->assertDontSee('href="#"', false)
            ->assertSee('profissaoList', false)
            ->assertSee('assets/css/laravel-utilities.css', false)
            ->assertDontSee('cdn.tailwindcss.com', false);
    }

    public function test_cadastra_cliente_com_relacionamentos_e_auditoria_sem_cpf_em_texto_puro(): void
    {
        $admin = Usuario::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/clientes', $this->validPayload());

        $response->assertRedirect('/clientes/1');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('clientes', [
            'codigo' => 'CLI-000001',
            'nome' => 'Maria da Silva',
            'cpf_cnpj' => '52998224725',
            'email_padrao' => 'maria@example.com',
            'celular_padrao' => '(11) 99999-8888',
        ]);
        $this->assertDatabaseHas('cliente_enderecos', [
            'cep' => '01310930',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'padrao' => true,
        ]);
        $this->assertDatabaseHas('cliente_telefones', [
            'tipo' => 'WHATSAPP',
            'numero' => '(11) 99999-8888',
            'padrao' => true,
        ]);
        $this->assertDatabaseHas('cliente_emails', [
            'email' => 'maria@example.com',
            'padrao' => true,
        ]);
        $this->assertDatabaseHas('cliente_conjuge', [
            'nome' => 'João da Silva',
            'cpf' => '11144477735',
        ]);
        $this->assertDatabaseHas('cliente_cnh', [
            'numero_registro' => '12345678900',
            'categoria' => 'B',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'usuario' => 'usuario#'.$admin->id,
            'entidade' => 'clientes',
            'entidade_id' => 1,
            'acao' => 'CRIAR',
        ]);

        $auditJson = (string) DB::table('audit_log')->value('dados_depois');
        $this->assertStringNotContainsString('52998224725', $auditJson);
        $this->assertStringNotContainsString('11144477735', $auditJson);
        $this->assertStringNotContainsString('maria@example.com', $auditJson);
        $this->assertStringContainsString('[PROTEGIDO]', $auditJson);
    }

    public function test_rejeita_cpf_invalido_sem_gravar_cliente_ou_auditoria(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $payload = $this->validPayload();
        $payload['cpf_cnpj'] = '111.111.111-11';

        $response = $this->actingAs($admin)
            ->from('/clientes/novo')
            ->post('/clientes', $payload);

        $response->assertRedirect('/clientes/novo');
        $response->assertSessionHasErrors('cpf_cnpj');
        $this->assertDatabaseCount('clientes', 0);
        $this->assertDatabaseCount('audit_log', 0);
    }

    public function test_rejeita_email_invalido_no_servidor(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $payload = $this->validPayload();
        $payload['emails'][0]['email'] = 'email-invalido';

        $response = $this->actingAs($admin)
            ->from('/clientes/novo')
            ->post('/clientes', $payload);

        $response->assertRedirect('/clientes/novo');
        $response->assertSessionHasErrors('emails.0.email');
        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_produtor_cadastra_cliente_direto_na_propria_carteira(): void
    {
        $produtor = Produtor::create(['nome' => 'Produtor da carteira', 'ativo' => true]);
        $usuario = Usuario::factory()->produtor($produtor->id)->create();
        $payload = $this->validPayload();
        $payload['nome'] = 'Cliente do produtor';

        $this->actingAs($usuario)->post('/clientes', $payload)->assertRedirect('/clientes/1');

        $this->assertDatabaseHas('clientes', [
            'nome' => 'Cliente do produtor',
            'produtor_id' => $produtor->id,
        ]);
    }

    public function test_rejeita_cpf_duplicado_e_mantem_apenas_um_cliente(): void
    {
        $admin = Usuario::factory()->admin()->create();

        $this->actingAs($admin)->post('/clientes', $this->validPayload())->assertRedirect('/clientes/1');

        $duplicado = $this->validPayload();
        $duplicado['nome'] = 'Outra pessoa com o mesmo CPF';

        $this->actingAs($admin)
            ->from('/clientes/novo')
            ->post('/clientes', $duplicado)
            ->assertRedirect('/clientes/novo')
            ->assertSessionHasErrors('cpf_cnpj');

        $this->assertDatabaseCount('clientes', 1);
        $this->assertDatabaseCount('audit_log', 1);
    }

    public function test_edita_cliente_e_relacionamentos_com_auditoria(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $this->actingAs($admin)->post('/clientes', $this->validPayload());
        $cliente = Cliente::firstOrFail();

        $this->actingAs($admin)
            ->get("/clientes/{$cliente->id}/editar")
            ->assertOk()
            ->assertSee('Editar cliente')
            ->assertSee('Maria da Silva')
            ->assertSee('João da Silva')
            ->assertSee('Salvar alterações');

        $alterado = $this->validPayload();
        $alterado['nome'] = 'Maria da Silva Atualizada';
        $alterado['intermedio'] = 'Instagram';
        $alterado['conjuge']['nome'] = 'João Atualizado';
        $alterado['telefones'][0]['numero'] = '(11) 98888-7777';
        $alterado['emails'][0]['email'] = 'nova@example.com';

        $this->actingAs($admin)
            ->put("/clientes/{$cliente->id}", $alterado)
            ->assertRedirect("/clientes/{$cliente->id}")
            ->assertSessionHas('status');

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nome' => 'Maria da Silva Atualizada',
            'cpf_cnpj' => '52998224725',
            'intermedio' => 'Instagram',
            'celular_padrao' => '(11) 98888-7777',
            'email_padrao' => 'nova@example.com',
        ]);
        $this->assertDatabaseHas('cliente_conjuge', [
            'cliente_id' => $cliente->id,
            'nome' => 'João Atualizado',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'entidade' => 'clientes',
            'entidade_id' => $cliente->id,
            'acao' => 'ALTERAR',
        ]);
        $this->assertDatabaseCount('audit_log', 2);

        $auditJson = (string) DB::table('audit_log')
            ->where('acao', 'ALTERAR')
            ->value('dados_depois');
        $this->assertStringNotContainsString('52998224725', $auditJson);
        $this->assertStringNotContainsString('nova@example.com', $auditJson);
    }

    public function test_permite_corrigir_registro_legado_que_ja_possui_cpf_duplicado(): void
    {
        $admin = Usuario::factory()->admin()->create();
        $primeiro = Cliente::create([
            'codigo' => 'CLI-LEGADO-1',
            'pessoa' => 'PF',
            'tipo_cliente' => 'PROSPECT',
            'status' => 'ATIVO',
            'nome' => 'Cliente legado um',
            'cpf_cnpj' => '52998224725',
        ]);
        Cliente::create([
            'codigo' => 'CLI-LEGADO-2',
            'pessoa' => 'PF',
            'tipo_cliente' => 'PROSPECT',
            'status' => 'ATIVO',
            'nome' => 'Cliente legado dois',
            'cpf_cnpj' => '52998224725',
        ]);

        $payload = $this->validPayload();
        $payload['nome'] = 'Cliente legado corrigido';

        $this->actingAs($admin)
            ->put("/clientes/{$primeiro->id}", $payload)
            ->assertRedirect("/clientes/{$primeiro->id}");

        $this->assertDatabaseHas('clientes', [
            'id' => $primeiro->id,
            'nome' => 'Cliente legado corrigido',
            'cpf_cnpj' => '52998224725',
        ]);
        $this->assertDatabaseCount('clientes', 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'pessoa' => 'PF',
            'nome' => 'Maria da Silva',
            'cpf_cnpj' => '529.982.247-25',
            'nascimento' => '1988-04-15',
            'estado_civil' => 'CASADO',
            'sexo' => 'F',
            'profissao' => 'Professora',
            'faixa_renda' => 'De R$ 5.000,01 a R$ 10.000',
            'tipo_cliente' => 'PROSPECT',
            'intermedio' => 'Indicação de cliente',
            'conjuge' => [
                'nome' => 'João da Silva',
                'cpf' => '111.444.777-35',
                'nascimento' => '1987-03-10',
            ],
            'tem_cnh' => '1',
            'cnh' => [
                'numero_registro' => '12345678900',
                'categoria' => 'b',
                'validade' => '2030-10-20',
                'primeira_habilitacao' => '2007-02-01',
            ],
            'endereco_padrao' => 0,
            'enderecos' => [[
                'tipo' => 'RESIDENCIAL',
                'cep' => '01310-930',
                'logradouro' => 'Avenida Paulista',
                'numero' => '1000',
                'complemento' => 'Apto 10',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'uf' => 'sp',
            ]],
            'telefones' => [[
                'tipo' => 'WHATSAPP',
                'numero' => '(11) 99999-8888',
            ]],
            'emails' => [[
                'email' => 'MARIA@EXAMPLE.COM',
            ]],
        ];
    }
}
