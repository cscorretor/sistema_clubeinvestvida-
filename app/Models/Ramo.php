<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ramo extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'grupo'];

    public function apolices(): HasMany
    {
        return $this->hasMany(Apolice::class);
    }
}
