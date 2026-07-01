<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    protected $fillable = [
        'codigo',
        'pessoa',
        'tipo_cliente',
        'status',
        'produtor_id',
        'intermedio',
        'nome',
        'cpf_cnpj',
        'doc_tipo',
        'doc_orgao',
        'doc_numero',
        'doc_emissao',
        'doc_validade',
        'profissao',
        'estado_civil',
        'nascimento',
        'sexo',
        'faixa_renda',
        'nome_fantasia',
        'inscricao_est',
        'data_abertura',
        'apelido',
        'email_padrao',
        'celular_padrao',
        'observacoes',
        'data_cadastro',
    ];

    protected function casts(): array
    {
        return [
            'nascimento' => 'date',
            'doc_emissao' => 'date',
            'doc_validade' => 'date',
            'data_abertura' => 'date',
            'data_cadastro' => 'date',
        ];
    }

    public function produtor(): BelongsTo
    {
        return $this->belongsTo(Produtor::class);
    }

    public function conjuge(): HasOne
    {
        return $this->hasOne(ClienteConjuge::class);
    }

    public function cnh(): HasOne
    {
        return $this->hasOne(ClienteCnh::class);
    }

    public function enderecos(): HasMany
    {
        return $this->hasMany(ClienteEndereco::class);
    }

    public function telefones(): HasMany
    {
        return $this->hasMany(ClienteTelefone::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ClienteEmail::class);
    }

    public function scopeVisivelPara(Builder $query, Usuario $usuario): Builder
    {
        if ($usuario->isProdutor()) {
            $query->where('produtor_id', $usuario->produtor_id);
        }

        return $query;
    }
}
