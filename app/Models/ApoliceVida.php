<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApoliceVida extends Model
{
    protected $table = 'apolice_vidas';

    public $timestamps = false;

    protected $fillable = ['nome', 'parentesco', 'nascimento', 'capital'];

    protected function casts(): array
    {
        return [
            'nascimento' => 'date',
            'capital' => 'decimal:2',
        ];
    }

    public function apolice(): BelongsTo
    {
        return $this->belongsTo(Apolice::class);
    }
}
