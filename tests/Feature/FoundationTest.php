<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_and_minimum_seeders_are_operational(): void
    {
        $this->seed();

        $this->assertDatabaseCount('ramos', 5);
        $this->assertDatabaseCount('produtores', 3);
        $this->assertDatabaseHas('usuarios', [
            'email' => 'admin@clubeinvestvida.local',
            'perfil' => 'ADMIN',
        ]);

        $hash = DB::table('usuarios')->value('senha_hash');
        $this->assertTrue(Hash::check('TestPassword!123', $hash));

        $this->seed();

        $this->assertSame(
            $hash,
            DB::table('usuarios')->value('senha_hash'),
            'Executar o seeder novamente não deve redefinir a senha do administrador.',
        );
    }
}
