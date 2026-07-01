<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produtor extends Model
{
    protected $table = 'produtores';

    protected $fillable = ['nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
}
