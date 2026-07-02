<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApoliceCobertura extends Model
{
    protected $table = 'apolice_coberturas';

    public $timestamps = false;

    protected $fillable = ['descricao', 'capital'];

    protected function casts(): array
    {
        return ['capital' => 'decimal:2'];
    }

    public function apolice(): BelongsTo
    {
        return $this->belongsTo(Apolice::class);
    }
}
