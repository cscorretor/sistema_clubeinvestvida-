<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seguradora extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function apolices(): HasMany
    {
        return $this->hasMany(Apolice::class);
    }
}
