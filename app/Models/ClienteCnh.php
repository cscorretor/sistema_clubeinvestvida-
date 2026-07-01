<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteCnh extends Model
{
    protected $table = 'cliente_cnh';

    protected $primaryKey = 'cliente_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['numero_registro', 'categoria', 'validade', 'primeira_habilitacao'];

    protected function casts(): array
    {
        return [
            'validade' => 'date',
            'primeira_habilitacao' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
