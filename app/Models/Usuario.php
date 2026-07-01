<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory;

    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha_hash',
        'perfil',
        'produtor_id',
        'duas_etapas',
        'ativo',
    ];

    protected $hidden = [
        'senha_hash',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'senha_hash' => 'hashed',
            'duas_etapas' => 'boolean',
            'ativo' => 'boolean',
            'ultimo_acesso' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Usuario $usuario): void {
            $usuario->duas_etapas = filled($usuario->two_factor_secret)
                && filled($usuario->two_factor_confirmed_at);
        });
    }

    public function getAuthPasswordName(): string
    {
        return 'senha_hash';
    }

    public function produtor(): BelongsTo
    {
        return $this->belongsTo(Produtor::class);
    }

    public function permissoes(): HasMany
    {
        return $this->hasMany(Permissao::class);
    }

    public function isAdmin(): bool
    {
        return $this->perfil === 'ADMIN';
    }

    public function isProdutor(): bool
    {
        return $this->perfil === 'PRODUTOR';
    }

    public function pode(string $modulo, string $acao = 'pode_ver'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! in_array($acao, ['pode_ver', 'pode_editar'], true)) {
            return false;
        }

        return (bool) $this->permissoes()
            ->where('modulo', $modulo)
            ->value($acao);
    }
}
