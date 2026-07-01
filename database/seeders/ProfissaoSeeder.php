<?php

namespace Database\Seeders;

use App\Models\Profissao;
use Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfissaoSeeder extends Seeder
{
    private const TAMANHO_LOTE = 500;

    public function run(): void
    {
        $this->importarOcupacoes();
        $this->importarSinonimos();
    }

    private function importarOcupacoes(): void
    {
        $lote = [];

        foreach ($this->lerCsv(database_path('data/cbo2002-ocupacao.csv')) as [$codigo, $titulo]) {
            $lote[] = [
                'codigo_cbo' => $codigo,
                'titulo' => $titulo,
                'titulo_busca' => Profissao::normalizarBusca($titulo),
            ];

            if (count($lote) === self::TAMANHO_LOTE) {
                $this->gravarOcupacoes($lote);
                $lote = [];
            }
        }

        $this->gravarOcupacoes($lote);
    }

    private function importarSinonimos(): void
    {
        $idsPorCodigo = DB::table('profissoes')->pluck('id', 'codigo_cbo');
        $lote = [];

        foreach ($this->lerCsv(database_path('data/cbo2002-sinonimo.csv')) as [$codigo, $titulo]) {
            $profissaoId = $idsPorCodigo->get($codigo);

            if ($profissaoId === null) {
                continue;
            }

            $lote[] = [
                'profissao_id' => $profissaoId,
                'titulo' => $titulo,
                'titulo_busca' => Profissao::normalizarBusca($titulo),
            ];

            if (count($lote) === self::TAMANHO_LOTE) {
                DB::table('profissao_sinonimos')->insertOrIgnore($lote);
                $lote = [];
            }
        }

        if ($lote !== []) {
            DB::table('profissao_sinonimos')->insertOrIgnore($lote);
        }
    }

    /**
     * @param  array<int, array{codigo_cbo: string, titulo: string, titulo_busca: string}>  $lote
     */
    private function gravarOcupacoes(array $lote): void
    {
        if ($lote === []) {
            return;
        }

        DB::table('profissoes')->upsert(
            $lote,
            ['codigo_cbo'],
            ['titulo', 'titulo_busca'],
        );
    }

    /**
     * @return Generator<int, array{0: string, 1: string}>
     */
    private function lerCsv(string $caminho): Generator
    {
        $arquivo = fopen($caminho, 'rb');

        if ($arquivo === false) {
            throw new RuntimeException("Não foi possível abrir o catálogo CBO: {$caminho}");
        }

        try {
            fgetcsv($arquivo, null, ';');

            while (($linha = fgetcsv($arquivo, null, ';')) !== false) {
                if (count($linha) < 2) {
                    continue;
                }

                $codigo = trim($linha[0]);
                $titulo = $this->paraUtf8(trim($linha[1]));

                if (preg_match('/^\d{6}$/', $codigo) !== 1 || $titulo === '') {
                    continue;
                }

                yield [$codigo, $titulo];
            }
        } finally {
            fclose($arquivo);
        }
    }

    private function paraUtf8(string $valor): string
    {
        if (mb_check_encoding($valor, 'UTF-8')) {
            return $valor;
        }

        return mb_convert_encoding($valor, 'UTF-8', 'Windows-1252');
    }
}
