<?php

namespace App\Http\Controllers;

use App\Models\Apolice;
use App\Models\ApoliceParcela;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof Usuario, 403);

        $clientes = Cliente::query()->visivelPara($usuario);
        $apolices = Apolice::query()
            ->whereHas('cliente', fn (Builder $query) => $query->visivelPara($usuario));

        $hoje = today();
        $limiteRenovacao = $hoje->copy()->addDays(30);
        $inicioGrafico = $hoje->copy()->startOfMonth()->subMonths(5);

        $apolicesRecentes = (clone $apolices)
            ->where('created_at', '>=', $inicioGrafico)
            ->get(['created_at']);

        $producaoMensal = collect(range(5, 0))
            ->map(function (int $mesesAtras) use ($hoje, $apolicesRecentes): array {
                $mes = $hoje->copy()->startOfMonth()->subMonths($mesesAtras);

                return [
                    'chave' => $mes->format('Y-m'),
                    'label' => mb_strtoupper(mb_substr($mes->translatedFormat('M'), 0, 3)),
                    'total' => $apolicesRecentes
                        ->filter(fn (Apolice $apolice): bool => $apolice->created_at?->format('Y-m') === $mes->format('Y-m'))
                        ->count(),
                ];
            });

        $maiorProducao = max(1, (int) $producaoMensal->max('total'));
        $producaoMensal = $producaoMensal
            ->map(fn (array $item): array => [
                ...$item,
                'percentual' => max(6, (int) round(($item['total'] / $maiorProducao) * 100)),
            ]);

        $carteiraPorRamo = (clone $apolices)
            ->with('ramo:id,nome')
            ->get(['id', 'ramo_id'])
            ->groupBy(fn (Apolice $apolice): string => $apolice->ramo?->nome ?? 'Sem ramo')
            ->map(fn (Collection $items, string $nome): array => ['nome' => $nome, 'total' => $items->count()])
            ->sortByDesc('total')
            ->values();

        $maiorRamo = max(1, (int) $carteiraPorRamo->max('total'));
        $carteiraPorRamo = $carteiraPorRamo
            ->map(fn (array $item): array => [
                ...$item,
                'percentual' => max(5, (int) round(($item['total'] / $maiorRamo) * 100)),
            ]);

        $renovacoes = (clone $apolices)
            ->with(['cliente:id,nome', 'ramo:id,nome', 'seguradora:id,nome'])
            ->whereIn('status', ['ATIVO', 'RENOVACAO'])
            ->whereBetween('fim_vigencia', [$hoje, $limiteRenovacao])
            ->orderBy('fim_vigencia')
            ->limit(6)
            ->get();

        $aniversariantes = (clone $clientes)
            ->where('pessoa', 'PF')
            ->where('status', 'ATIVO')
            ->whereNotNull('nascimento')
            ->get(['id', 'nome', 'nascimento', 'celular_padrao'])
            ->map(function (Cliente $cliente) use ($hoje): array {
                $proximo = $this->proximoAniversario($cliente->nascimento, $hoje);
                $dias = (int) $hoje->diffInDays($proximo);

                return [
                    'cliente' => $cliente,
                    'data' => $proximo,
                    'dias' => $dias,
                    'idade' => $proximo->year - $cliente->nascimento->year,
                ];
            })
            ->filter(fn (array $item): bool => $item['dias'] <= 30)
            ->sortBy('dias')
            ->values();

        $comissaoAReceber = ApoliceParcela::query()
            ->whereHas('apolice.cliente', fn (Builder $query) => $query->visivelPara($usuario))
            ->where('status', 'ABERTO')
            ->whereBetween('vencimento', [$hoje, $hoje->copy()->addDays(90)])
            ->sum('valor_comissao');

        return view('dashboard', [
            'clientesAtivos' => (clone $clientes)->where('status', 'ATIVO')->count(),
            'apolicesAtivas' => (clone $apolices)->whereIn('status', ['ATIVO', 'RENOVACAO'])->count(),
            'comissaoAReceber' => (float) $comissaoAReceber,
            'renovacoesCount' => (clone $apolices)
                ->whereIn('status', ['ATIVO', 'RENOVACAO'])
                ->whereBetween('fim_vigencia', [$hoje, $limiteRenovacao])
                ->count(),
            'producaoMensal' => $producaoMensal,
            'carteiraPorRamo' => $carteiraPorRamo,
            'renovacoes' => $renovacoes,
            'aniversariantes' => $aniversariantes,
        ]);
    }

    private function proximoAniversario(Carbon $nascimento, Carbon $hoje): Carbon
    {
        $dia = $nascimento->day;
        $mes = $nascimento->month;
        $ano = $hoje->year;

        $proximo = Carbon::create($ano, $mes, min($dia, Carbon::create($ano, $mes)->daysInMonth))
            ->startOfDay();

        if ($proximo->lt($hoje)) {
            $ano++;
            $proximo = Carbon::create($ano, $mes, min($dia, Carbon::create($ano, $mes)->daysInMonth))
                ->startOfDay();
        }

        return $proximo;
    }
}
