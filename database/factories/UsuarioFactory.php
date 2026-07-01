<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<Usuario> */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'senha_hash' => static::$password ??= Hash::make('Password!123'),
            'perfil' => 'COMUM',
            'duas_etapas' => false,
            'ativo' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['perfil' => 'ADMIN']);
    }

    public function produtor(int $produtorId): static
    {
        return $this->state(fn () => [
            'perfil' => 'PRODUTOR',
            'produtor_id' => $produtorId,
        ]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['ativo' => false]);
    }
}
