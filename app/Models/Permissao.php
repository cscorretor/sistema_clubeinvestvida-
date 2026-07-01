<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permissao extends Model
{
    protected $table = 'permissoes';

    public $timestamps = false;

    protected $fillable = ['usuario_id', 'modulo', 'pode_ver', 'pode_editar'];

    protected function casts(): array
    {
        return [
            'pode_ver' => 'boolean',
            'pode_editar' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
