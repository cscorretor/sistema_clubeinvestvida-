<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamado extends Model
{
    protected $table = 'chamados';

    protected $fillable = [
        'tipo',
        'subtipo',
        'cliente_id',
        'apolice_id',
        'descricao',
        'status',
        'prioridade',
        'data_resolucao',
        'responsavel_id',
        'quem_fecha',
        'created_by',
    ];

    protected function casts(): array
    {
        return ['data_resolucao' => 'date'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
