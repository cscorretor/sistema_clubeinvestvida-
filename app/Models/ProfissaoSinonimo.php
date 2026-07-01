<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfissaoSinonimo extends Model
{
    public $timestamps = false;

    protected $table = 'profissao_sinonimos';

    protected $fillable = [
        'profissao_id',
        'titulo',
        'titulo_busca',
    ];

    public function profissao(): BelongsTo
    {
        return $this->belongsTo(Profissao::class);
    }
}
