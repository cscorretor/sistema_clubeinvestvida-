<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Cliente extends Model
{
    public const ORIGENS = [
        'Indicação de cliente',
        'Indicação de parceiro',
        'Google (orgânico)',
        'Google Ads',
        'Instagram',
        'Facebook',
        'WhatsApp',
        'E-mail marketing',
        'Campanha',
        'Site',
        'Evento',
        'Prospecção ativa',
        'Cliente existente / renovação',
        'Outro',
    ];

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

    public function apolices(): HasMany
    {
        return $this->hasMany(Apolice::class);
    }

    public function chamados(): HasMany
    {
        return $this->hasMany(Chamado::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'entidade_id')
            ->where('entidade', 'clientes');
    }

    public function iniciais(): string
    {
        return Str::of($this->nome)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $parte): string => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
    }

    public function documentoMascarado(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->cpf_cnpj);

        if ($this->pessoa === 'PJ' && strlen($digits) === 14) {
            return '**.***.***/****-'.substr($digits, -2);
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3).'.***.***-'.substr($digits, -2);
        }

        return 'Não informado';
    }

    public function scopeVisivelPara(Builder $query, Usuario $usuario): Builder
    {
        if ($usuario->isProdutor()) {
            $query->where('produtor_id', $usuario->produtor_id);
        }

        return $query;
    }
}
