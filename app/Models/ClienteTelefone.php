<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteTelefone extends Model
{
    protected $table = 'cliente_telefones';

    public $timestamps = false;

    protected $fillable = ['padrao', 'tipo', 'numero', 'observacao'];

    protected function casts(): array
    {
        return ['padrao' => 'boolean'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
