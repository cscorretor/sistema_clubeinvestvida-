<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteContato extends Model
{
    protected $table = 'cliente_contatos';

    public $timestamps = false;

    protected $fillable = ['principal', 'nome', 'cargo', 'email', 'telefone'];

    protected function casts(): array
    {
        return ['principal' => 'boolean'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
