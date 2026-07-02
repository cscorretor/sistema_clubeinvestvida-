<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteConjuge extends Model
{
    protected $table = 'cliente_conjuge';

    protected $primaryKey = 'cliente_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['nome', 'cpf', 'nascimento'];

    protected function casts(): array
    {
        return ['nascimento' => 'date'];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function documentoMascarado(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->cpf);

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3).'.***.***-'.substr($digits, -2);
        }

        return 'Não informado';
    }
}
