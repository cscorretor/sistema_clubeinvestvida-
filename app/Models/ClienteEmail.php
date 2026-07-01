<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteEmail extends Model
{
    protected $table = 'cliente_emails';

    public $timestamps = false;

    protected $fillable = ['padrao', 'email', 'observacao'];

    protected function casts(): array
    {
        return ['padrao' => 'boolean'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
