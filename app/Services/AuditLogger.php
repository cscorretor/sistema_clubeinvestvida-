<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Usuario;

class AuditLogger
{
    public function record(
        Usuario $usuario,
        string $entidade,
        int $entidadeId,
        string $acao,
        ?array $antes,
        ?array $depois,
        ?string $ip,
    ): AuditLog {
        return AuditLog::create([
            'usuario' => 'usuario#'.$usuario->getKey(),
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'acao' => $acao,
            'dados_antes' => $this->sanitize($antes),
            'dados_depois' => $this->sanitize($depois),
            'ip' => $ip,
        ]);
    }

    private function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match('/cpf|cnpj|email|telefone|celular|saude|senha|secret|token/i', $key)) {
                $data[$key] = '[PROTEGIDO]';

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}
