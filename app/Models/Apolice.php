<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apolice extends Model
{
    public const PRODUTOS_SUPORTADOS = ['Vida', 'Previdência', 'Saúde', 'Residencial'];

    public const COBERTURAS_POR_PRODUTO = [
        'Vida' => ['Morte', 'Morte acidental', 'Invalidez permanente', 'Doenças graves', 'Assistência funeral', 'Diária de internação'],
        'Previdência' => ['PGBL', 'VGBL', 'Renda mensal', 'Pecúlio', 'Portabilidade'],
        'Saúde' => ['Ambulatorial', 'Hospitalar', 'Obstetrícia', 'Odontológico', 'Reembolso', 'Cobertura nacional'],
        'Residencial' => ['Incêndio', 'Danos elétricos', 'Roubo e furto', 'Vendaval', 'Responsabilidade civil', 'Assistência 24 horas'],
    ];

    protected $table = 'apolices';

    protected $fillable = [
        'cliente_id',
        'ramo_id',
        'seguradora_id',
        'produtor_id',
        'num_proposta',
        'num_apolice',
        'status',
        'inicio_vigencia',
        'fim_vigencia',
        'capital_segurado',
        'dados_produto',
        'tipo_proposta',
        'apolice_origem_id',
    ];

    protected function casts(): array
    {
        return [
            'inicio_vigencia' => 'date',
            'fim_vigencia' => 'date',
            'capital_segurado' => 'decimal:2',
            'dados_produto' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ramo(): BelongsTo
    {
        return $this->belongsTo(Ramo::class);
    }

    public function seguradora(): BelongsTo
    {
        return $this->belongsTo(Seguradora::class);
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(ApoliceParcela::class);
    }

    public function vidas(): HasMany
    {
        return $this->hasMany(ApoliceVida::class);
    }

    public function beneficiarios(): HasMany
    {
        return $this->hasMany(ApoliceBeneficiario::class);
    }

    public function coberturas(): HasMany
    {
        return $this->hasMany(ApoliceCobertura::class);
    }
}
