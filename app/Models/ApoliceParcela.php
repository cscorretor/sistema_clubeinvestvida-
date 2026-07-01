<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApoliceParcela extends Model
{
    protected $table = 'apolice_parcelas';

    public $timestamps = false;

    protected $fillable = [
        'numero',
        'vencimento',
        'valor_cliente',
        'valor_comissao',
        'percentual_comissao',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'valor_cliente' => 'decimal:2',
            'valor_comissao' => 'decimal:2',
            'percentual_comissao' => 'decimal:2',
        ];
    }

    public function apolice(): BelongsTo
    {
        return $this->belongsTo(Apolice::class);
    }
}
