<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'admin@clubeinvestvida.local');

        if (DB::table('usuarios')->where('email', $email)->exists()) {
            return;
        }

        $password = env('SEED_ADMIN_PASSWORD');

        if (! $password) {
            if (app()->environment('testing')) {
                $password = 'TestPassword!123';
            } else {
                throw new RuntimeException('Defina SEED_ADMIN_PASSWORD antes de executar o seeder.');
            }
        }

        DB::table('usuarios')->insert([
            'nome' => env('SEED_ADMIN_NAME', 'Administrador'),
            'email' => $email,
            'senha_hash' => Hash::make($password),
            'perfil' => 'ADMIN',
            'duas_etapas' => false,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
