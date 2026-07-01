<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apolice extends Model
{
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
        'tipo_proposta',
        'apolice_origem_id',
    ];

    protected function casts(): array
    {
        return [
            'inicio_vigencia' => 'date',
            'fim_vigencia' => 'date',
            'capital_segurado' => 'decimal:2',
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
}
