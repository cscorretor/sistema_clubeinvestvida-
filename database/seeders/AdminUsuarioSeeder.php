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
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $password) {
            if (app()->environment('testing')) {
                $password = 'TestPassword!123';
            } else {
                throw new RuntimeException('Defina SEED_ADMIN_PASSWORD antes de executar o seeder.');
            }
        }

        DB::table('usuarios')->updateOrInsert(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@clubeinvestvida.local')],
            [
                'nome' => env('SEED_ADMIN_NAME', 'Administrador'),
                'senha_hash' => Hash::make($password),
                'perfil' => 'ADMIN',
                'duas_etapas' => false,
                'ativo' => true,
            ],
        );
    }
}
