<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function create(): View
    {
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $dados = $request->validated();
        $usuario = $request->user();

        abort_unless($usuario instanceof Usuario, 403);

        $cliente = DB::transaction(function () use ($dados, $usuario, $request, $auditLogger): Cliente {
            $enderecos = $this->filledRows(
                $dados['enderecos'] ?? [],
                ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'],
            );
            $telefones = $this->filledRows($dados['telefones'] ?? [], ['numero']);
            $emails = $this->filledRows($dados['emails'] ?? [], ['email']);
            $firstPhoneKey = array_key_first($telefones);
            $firstEmailKey = array_key_first($emails);

            $cliente = Cliente::create([
                'pessoa' => $dados['pessoa'],
                'tipo_cliente' => $dados['tipo_cliente'],
                'status' => 'ATIVO',
                'produtor_id' => $usuario->isProdutor() ? $usuario->produtor_id : null,
                'intermedio' => $dados['intermedio'] ?? null,
                'nome' => $dados['nome'],
                'cpf_cnpj' => $dados['cpf_cnpj'],
                'profissao' => $dados['profissao'] ?? null,
                'estado_civil' => $dados['estado_civil'] ?? null,
                'nascimento' => $dados['nascimento'] ?? null,
                'sexo' => $dados['sexo'] ?? null,
                'faixa_renda' => $dados['faixa_renda'] ?? null,
                'celular_padrao' => $firstPhoneKey !== null ? $telefones[$firstPhoneKey]['numero'] : null,
                'email_padrao' => $firstEmailKey !== null ? $emails[$firstEmailKey]['email'] : null,
                'data_cadastro' => now()->toDateString(),
            ]);

            $cliente->forceFill(['codigo' => sprintf('CLI-%06d', $cliente->getKey())])->saveQuietly();

            $conjuge = $dados['conjuge'] ?? [];
            if ($this->hasAnyValue($conjuge, ['nome', 'cpf', 'nascimento'])) {
                $cliente->conjuge()->create([
                    'nome' => $conjuge['nome'] ?? null,
                    'cpf' => $conjuge['cpf'] ?? null,
                    'nascimento' => $conjuge['nascimento'] ?? null,
                ]);
            }

            $cnh = $dados['cnh'] ?? [];
            if (($dados['tem_cnh'] ?? false) && $this->hasAnyValue($cnh, ['numero_registro', 'categoria', 'validade', 'primeira_habilitacao'])) {
                $cliente->cnh()->create([
                    'numero_registro' => $cnh['numero_registro'] ?? null,
                    'categoria' => isset($cnh['categoria']) ? mb_strtoupper($cnh['categoria']) : null,
                    'validade' => $cnh['validade'] ?? null,
                    'primeira_habilitacao' => $cnh['primeira_habilitacao'] ?? null,
                ]);
            }

            $selectedAddress = (int) ($dados['endereco_padrao'] ?? array_key_first($enderecos) ?? 0);
            $hasSelectedAddress = array_key_exists($selectedAddress, $enderecos);

            foreach ($enderecos as $index => $endereco) {
                $cliente->enderecos()->create([
                    'padrao' => $hasSelectedAddress ? $index === $selectedAddress : $index === array_key_first($enderecos),
                    'tipo' => $endereco['tipo'] ?? 'RESIDENCIAL',
                    'cep' => $endereco['cep'] ?? null,
                    'logradouro' => $endereco['logradouro'] ?? null,
                    'numero' => $endereco['numero'] ?? null,
                    'complemento' => $endereco['complemento'] ?? null,
                    'bairro' => $endereco['bairro'] ?? null,
                    'cidade' => $endereco['cidade'] ?? null,
                    'uf' => $endereco['uf'] ?? null,
                ]);
            }

            foreach ($telefones as $index => $telefone) {
                $cliente->telefones()->create([
                    'padrao' => $index === array_key_first($telefones),
                    'tipo' => $telefone['tipo'] ?? 'CELULAR',
                    'numero' => $telefone['numero'],
                ]);
            }

            foreach ($emails as $index => $email) {
                $cliente->emails()->create([
                    'padrao' => $index === array_key_first($emails),
                    'email' => $email['email'],
                ]);
            }

            $after = $cliente->fresh()
                ->load(['conjuge', 'cnh', 'enderecos', 'telefones', 'emails'])
                ->toArray();

            $auditLogger->record(
                $usuario,
                'clientes',
                (int) $cliente->getKey(),
                'CRIAR',
                null,
                $after,
                $request->ip(),
            );

            return $cliente;
        });

        return to_route('clientes.create')
            ->with('status', "Cliente {$cliente->codigo} cadastrado com sucesso.");
    }

    /**
     * @param  array<int|string, mixed>  $rows
     * @param  list<string>  $keys
     * @return array<int|string, array<string, mixed>>
     */
    private function filledRows(array $rows, array $keys): array
    {
        return array_filter(
            $rows,
            fn (mixed $row): bool => is_array($row) && $this->hasAnyValue($row, $keys),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $keys
     */
    private function hasAnyValue(array $data, array $keys): bool
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && trim((string) $data[$key]) !== '') {
                return true;
            }
        }

        return false;
    }
}
