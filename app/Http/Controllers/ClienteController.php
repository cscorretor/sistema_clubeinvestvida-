<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
use App\Models\ClienteEndereco;
use App\Models\Ramo;
use App\Models\Usuario;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Cliente::class);

        $filters = $request->validate([
            'busca' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', Rule::in(['TODOS', 'EFETIVO', 'PROSPECT', 'RELACIONAMENTO', 'CONDUTOR', 'LOCADOR'])],
            'status' => ['nullable', Rule::in(['TODOS', 'ATIVO', 'INATIVO'])],
            'ramo' => ['nullable', 'integer', 'exists:ramos,id'],
            'cidade' => ['nullable', 'string', 'max:80'],
        ]);

        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        $status = $filters['status'] ?? 'ATIVO';
        $busca = trim((string) ($filters['busca'] ?? ''));
        $digits = preg_replace('/\D/', '', $busca);

        $clientes = Cliente::query()
            ->visivelPara($usuario)
            ->with([
                'enderecos' => fn ($query) => $query->orderByDesc('padrao')->orderBy('id'),
                'apolices.ramo',
            ])
            ->withMin([
                'apolices as proxima_renovacao' => fn ($query) => $query
                    ->whereIn('status', ['ATIVO', 'RENOVACAO'])
                    ->whereDate('fim_vigencia', '>=', today()),
            ], 'fim_vigencia')
            ->when($busca !== '', function ($query) use ($busca, $digits): void {
                $query->where(function ($query) use ($busca, $digits): void {
                    $query->where('nome', 'like', "%{$busca}%")
                        ->orWhere('codigo', 'like', "%{$busca}%");

                    if ($digits !== '') {
                        $query->orWhere('cpf_cnpj', 'like', "%{$digits}%");
                    }

                    $query->orWhereHas('telefones', function ($query) use ($busca, $digits): void {
                        $query->where('numero', 'like', "%{$busca}%");

                        if ($digits !== '') {
                            $query->orWhereRaw(
                                "REPLACE(REPLACE(REPLACE(REPLACE(numero, '(', ''), ')', ''), ' ', ''), '-', '') LIKE ?",
                                ["%{$digits}%"],
                            );
                        }
                    });
                });
            })
            ->when(($filters['tipo'] ?? 'TODOS') !== 'TODOS', fn ($query) => $query->where('tipo_cliente', $filters['tipo']))
            ->when($status !== 'TODOS', fn ($query) => $query->where('status', $status))
            ->when(isset($filters['ramo']), fn ($query) => $query->whereHas('apolices', fn ($query) => $query->where('ramo_id', $filters['ramo'])))
            ->when(isset($filters['cidade']), fn ($query) => $query->whereHas('enderecos', fn ($query) => $query->where('cidade', $filters['cidade'])))
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString();

        $cidades = ClienteEndereco::query()
            ->whereHas('cliente', fn ($query) => $query->visivelPara($usuario))
            ->whereNotNull('cidade')
            ->where('cidade', '<>', '')
            ->distinct()
            ->orderBy('cidade')
            ->pluck('cidade');

        $ramos = Ramo::query()
            ->where('grupo', 'PESSOAS')
            ->orderBy('nome')
            ->get();

        return view('clientes.index', [
            'clientes' => $clientes,
            'cidades' => $cidades,
            'ramos' => $ramos,
            'filters' => [...$filters, 'status' => $status],
        ]);
    }

    public function show(Request $request, int $cliente): View
    {
        Gate::authorize('viewAny', Cliente::class);

        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        $cliente = Cliente::query()
            ->visivelPara($usuario)
            ->with([
                'produtor',
                'conjuge',
                'cnh',
                'enderecos' => fn ($query) => $query->orderByDesc('padrao')->orderBy('id'),
                'telefones' => fn ($query) => $query->orderByDesc('padrao')->orderBy('id'),
                'emails' => fn ($query) => $query->orderByDesc('padrao')->orderBy('id'),
                'apolices' => fn ($query) => $query->orderByDesc('created_at'),
                'apolices.ramo',
                'apolices.seguradora',
                'apolices.parcelas',
                'chamados' => fn ($query) => $query->latest(),
                'auditLogs' => fn ($query) => $query->latest()->limit(20),
            ])
            ->findOrFail($cliente);

        Gate::authorize('view', $cliente);

        return view('clientes.show', compact('cliente'));
    }

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
