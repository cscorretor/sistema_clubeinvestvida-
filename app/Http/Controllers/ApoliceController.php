<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveApoliceRequest;
use App\Models\Apolice;
use App\Models\Cliente;
use App\Models\Ramo;
use App\Models\Seguradora;
use App\Models\Usuario;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApoliceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Apolice::class);

        $filters = $request->validate([
            'busca' => ['nullable', 'string', 'max:100'],
            'ramo' => ['nullable', 'integer', 'exists:ramos,id'],
            'status' => ['nullable', Rule::in(['TODOS', 'PROSPECCAO', 'EM_EMISSAO', 'ATIVO', 'RENOVACAO', 'CANCELADO', 'INATIVO'])],
        ]);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        $busca = trim((string) ($filters['busca'] ?? ''));
        $status = $filters['status'] ?? 'TODOS';

        $apolices = Apolice::query()
            ->with(['cliente', 'ramo', 'seguradora'])
            ->whereHas('cliente', fn ($query) => $query->visivelPara($usuario))
            ->when($busca !== '', function ($query) use ($busca): void {
                $query->where(function ($query) use ($busca): void {
                    $query->where('num_proposta', 'like', "%{$busca}%")
                        ->orWhere('num_apolice', 'like', "%{$busca}%")
                        ->orWhereHas('cliente', fn ($query) => $query->where('nome', 'like', "%{$busca}%"));
                });
            })
            ->when(isset($filters['ramo']), fn ($query) => $query->where('ramo_id', $filters['ramo']))
            ->when($status !== 'TODOS', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('apolices.index', [
            'apolices' => $apolices,
            'ramos' => $this->ramos(),
            'filters' => [...$filters, 'status' => $status],
        ]);
    }

    public function create(Request $request, int $cliente): View
    {
        $cliente = $this->findCliente($request, $cliente);
        Gate::authorize('create', [Apolice::class, $cliente]);

        return $this->formView($cliente, null, []);
    }

    public function store(
        SaveApoliceRequest $request,
        int $cliente,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $cliente = $this->findCliente($request, $cliente);
        Gate::authorize('create', [Apolice::class, $cliente]);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);
        $dados = $request->validated();

        $apolice = DB::transaction(function () use ($cliente, $usuario, $dados, $request, $auditLogger): Apolice {
            $apolice = Apolice::create([
                ...$this->mainData($dados),
                'cliente_id' => $cliente->getKey(),
                'produtor_id' => $usuario->isProdutor() ? $usuario->produtor_id : $cliente->produtor_id,
            ]);
            $this->syncRelations($apolice, $dados);
            $after = $this->snapshot($apolice);

            $auditLogger->record(
                $usuario,
                'apolices',
                (int) $apolice->getKey(),
                'CRIAR',
                null,
                $after,
                $request->ip(),
            );

            return $apolice;
        });

        $label = $apolice->num_apolice ? 'Apólice' : 'Proposta';

        return to_route('clientes.show', $cliente)
            ->with('status', "{$label} cadastrada com sucesso.");
    }

    public function edit(Request $request, int $apolice): View
    {
        $apolice = $this->findApolice($request, $apolice);
        Gate::authorize('update', $apolice);

        return $this->formView($apolice->cliente, $apolice, $this->formData($apolice));
    }

    public function update(
        SaveApoliceRequest $request,
        int $apolice,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        $apolice = $this->findApolice($request, $apolice);
        Gate::authorize('update', $apolice);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);
        $dados = $request->validated();

        DB::transaction(function () use ($apolice, $usuario, $dados, $request, $auditLogger): void {
            $before = $this->snapshot($apolice);
            $apolice->update($this->mainData($dados));
            $this->syncRelations($apolice, $dados);
            $after = $this->snapshot($apolice);

            $auditLogger->record(
                $usuario,
                'apolices',
                (int) $apolice->getKey(),
                'ALTERAR',
                $before,
                $after,
                $request->ip(),
            );
        });

        return to_route('clientes.show', $apolice->cliente)
            ->with('status', 'Proposta/apólice atualizada com sucesso.');
    }

    private function findCliente(Request $request, int $cliente): Cliente
    {
        Gate::authorize('viewAny', Cliente::class);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        return Cliente::query()
            ->visivelPara($usuario)
            ->with(['conjuge', 'enderecos'])
            ->findOrFail($cliente);
    }

    private function findApolice(Request $request, int $apolice): Apolice
    {
        Gate::authorize('viewAny', Apolice::class);
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        return Apolice::query()
            ->with(['cliente', 'ramo', 'seguradora', 'vidas', 'beneficiarios', 'coberturas', 'parcelas'])
            ->whereHas('cliente', fn ($query) => $query->visivelPara($usuario))
            ->findOrFail($apolice);
    }

    /**
     * @param  array<string, mixed>  $form
     */
    private function formView(Cliente $cliente, ?Apolice $apolice, array $form): View
    {
        return view('apolices.form', [
            'cliente' => $cliente,
            'apolice' => $apolice,
            'apoliceForm' => $form,
            'ramos' => $this->ramos(),
            'seguradoras' => Seguradora::query()->where('ativo', true)->orderBy('nome')->get(),
            'coberturasPorProduto' => Apolice::COBERTURAS_POR_PRODUTO,
        ]);
    }

    private function ramos()
    {
        return Ramo::query()
            ->whereIn('nome', Apolice::PRODUTOS_SUPORTADOS)
            ->orderByRaw("CASE nome WHEN 'Vida' THEN 1 WHEN 'Previdência' THEN 2 WHEN 'Saúde' THEN 3 WHEN 'Residencial' THEN 4 ELSE 5 END")
            ->get();
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function mainData(array $dados): array
    {
        $ramo = Ramo::query()->findOrFail($dados['ramo_id']);
        $productKeys = match ($ramo->nome) {
            'Previdência' => ['modalidade_previdencia', 'regime_tributario'],
            'Saúde' => ['acomodacao', 'abrangencia', 'coparticipacao'],
            'Residencial' => ['tipo_imovel', 'cep_imovel', 'endereco_imovel', 'cidade_imovel', 'uf_imovel'],
            default => [],
        };
        $productData = Arr::only($dados['dados_produto'] ?? [], $productKeys);

        return [
            'ramo_id' => $ramo->getKey(),
            'seguradora_id' => $dados['seguradora_id'],
            'num_proposta' => $dados['num_proposta'] ?? null,
            'num_apolice' => $dados['num_apolice'] ?? null,
            'status' => $dados['status'],
            'inicio_vigencia' => $dados['inicio_vigencia'] ?? null,
            'fim_vigencia' => $dados['fim_vigencia'] ?? null,
            'capital_segurado' => $dados['capital_segurado'] ?? null,
            'dados_produto' => $productData !== [] ? $productData : null,
            'tipo_proposta' => $dados['tipo_proposta'],
        ];
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function syncRelations(Apolice $apolice, array $dados): void
    {
        $apolice->vidas()->delete();
        foreach ($dados['vidas'] ?? [] as $vida) {
            $apolice->vidas()->create($vida);
        }

        $apolice->beneficiarios()->delete();
        foreach ($dados['beneficiarios'] ?? [] as $beneficiario) {
            $apolice->beneficiarios()->create($beneficiario);
        }

        $apolice->coberturas()->delete();
        foreach (array_unique($dados['coberturas']) as $cobertura) {
            $apolice->coberturas()->create([
                'descricao' => $cobertura,
                'capital' => $dados['capital_segurado'] ?? null,
            ]);
        }

        $primeiroVencimento = Carbon::parse($dados['primeiro_vencimento']);
        foreach (range(1, 12) as $numero) {
            $parcela = $apolice->parcelas()->firstOrNew(['numero' => $numero]);
            $parcela->vencimento = $primeiroVencimento->copy()->addMonthsNoOverflow($numero - 1);
            $parcela->valor_cliente = $dados['valor_mensal'];
            if (! $parcela->exists) {
                $parcela->status = 'ABERTO';
            }
            $parcela->save();
        }

        $apolice->parcelas()
            ->where('numero', '>', 12)
            ->where('status', '<>', 'LIQUIDADO')
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Apolice $apolice): array
    {
        return $apolice->fresh()
            ->load(['cliente', 'ramo', 'seguradora', 'vidas', 'beneficiarios', 'coberturas', 'parcelas'])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Apolice $apolice): array
    {
        $primeiraParcela = $apolice->parcelas->sortBy('numero')->first();

        return [
            'ramo_id' => $apolice->ramo_id,
            'seguradora_id' => $apolice->seguradora_id,
            'tipo_proposta' => $apolice->tipo_proposta,
            'num_proposta' => $apolice->num_proposta,
            'num_apolice' => $apolice->num_apolice,
            'status' => $apolice->status,
            'inicio_vigencia' => $apolice->inicio_vigencia?->toDateString(),
            'fim_vigencia' => $apolice->fim_vigencia?->toDateString(),
            'capital_segurado' => $apolice->capital_segurado,
            'dados_produto' => $apolice->dados_produto ?? [],
            'vidas' => $apolice->vidas->map(fn ($vida): array => [
                'nome' => $vida->nome,
                'parentesco' => $vida->parentesco,
                'nascimento' => $vida->nascimento?->toDateString(),
                'capital' => $vida->capital,
            ])->all(),
            'beneficiarios' => $apolice->beneficiarios->map(fn ($beneficiario): array => [
                'nome' => $beneficiario->nome,
                'parentesco' => $beneficiario->parentesco,
                'percentual' => $beneficiario->percentual,
            ])->all(),
            'coberturas' => $apolice->coberturas->pluck('descricao')->all(),
            'valor_mensal' => $primeiraParcela?->valor_cliente,
            'primeiro_vencimento' => $primeiraParcela?->vencimento?->toDateString(),
        ];
    }
}
