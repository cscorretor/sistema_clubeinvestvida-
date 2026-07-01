<?php

namespace App\Http\Controllers;

use App\Models\Profissao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfissaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $termo = Profissao::normalizarBusca($validated['q'] ?? '');

        if (mb_strlen($termo) < 3) {
            return response()->json(['data' => []]);
        }

        $inicio = $termo.'%';
        $qualquerPosicao = '%'.$termo.'%';

        $profissoes = Profissao::query()
            ->where(function ($query) use ($qualquerPosicao): void {
                $query
                    ->where('titulo_busca', 'like', $qualquerPosicao)
                    ->orWhereHas('sinonimos', function ($sinonimos) use ($qualquerPosicao): void {
                        $sinonimos->where('titulo_busca', 'like', $qualquerPosicao);
                    });
            })
            ->orderByRaw('CASE WHEN titulo_busca LIKE ? THEN 0 ELSE 1 END', [$inicio])
            ->orderBy('titulo')
            ->limit(10)
            ->get(['codigo_cbo', 'titulo'])
            ->map(fn (Profissao $profissao): array => [
                'codigo' => $profissao->codigo_cbo,
                'titulo' => $profissao->titulo,
            ]);

        return response()->json(['data' => $profissoes]);
    }
}
