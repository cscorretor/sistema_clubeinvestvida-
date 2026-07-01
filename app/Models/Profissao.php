<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Profissao extends Model
{
    public $timestamps = false;

    protected $table = 'profissoes';

    protected $fillable = [
        'codigo_cbo',
        'titulo',
        'titulo_busca',
    ];

    public function sinonimos(): HasMany
    {
        return $this->hasMany(ProfissaoSinonimo::class);
    }

    public static function normalizarBusca(string $valor): string
    {
        return Str::of($valor)
            ->lower()
            ->ascii()
            ->squish()
            ->toString();
    }
}
