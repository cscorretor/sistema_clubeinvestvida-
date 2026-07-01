<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cliente extends Model
{
    protected $fillable = [
        'codigo',
        'pessoa',
        'tipo_cliente',
        'status',
        'produtor_id',
        'nome',
        'cpf_cnpj',
        'email_padrao',
        'celular_padrao',
    ];

    public function produtor(): BelongsTo
    {
        return $this->belongsTo(Produtor::class);
    }

    public function scopeVisivelPara(Builder $query, Usuario $usuario): Builder
    {
        if ($usuario->isProdutor()) {
            $query->where('produtor_id', $usuario->produtor_id);
        }

        return $query;
    }
}
