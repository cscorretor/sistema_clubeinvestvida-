<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    public const UPDATED_AT = null;

    protected $fillable = [
        'usuario',
        'entidade',
        'entidade_id',
        'acao',
        'dados_antes',
        'dados_depois',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'dados_antes' => 'array',
            'dados_depois' => 'array',
        ];
    }
}
