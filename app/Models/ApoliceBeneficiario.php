<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApoliceBeneficiario extends Model
{
    protected $table = 'apolice_beneficiarios';

    public $timestamps = false;

    protected $fillable = ['nome', 'parentesco', 'percentual'];

    protected function casts(): array
    {
        return ['percentual' => 'decimal:2'];
    }

    public function apolice(): BelongsTo
    {
        return $this->belongsTo(Apolice::class);
    }
}
