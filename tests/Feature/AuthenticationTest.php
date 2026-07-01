<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_ativo_pode_entrar_com_a_tabela_usuarios(): void
    {
        $usuario = Usuario::factory()->create([
            'email' => 'usuario@clubeinvestvida.com',
            'senha_hash' => 'Password!123',
        ]);

        $response = $this->post('/login', [
            'email' => 'USUARIO@CLUBEINVESTVIDA.COM',
            'password' => 'Password!123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($usuario);
        $this->assertNotNull($usuario->fresh()->ultimo_acesso);
    }

    public function test_usuario_inativo_nao_pode_entrar(): void
    {
        Usuario::factory()->inativo()->create([
            'email' => 'inativo@clubeinvestvida.com',
            'senha_hash' => 'Password!123',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inativo@clubeinvestvida.com',
            'password' => 'Password!123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_com_2fa_precisa_confirmar_codigo_totp(): void
    {
        $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();
        $usuario = Usuario::factory()->create([
            'email' => 'seguro@clubeinvestvida.com',
            'senha_hash' => 'Password!123',
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode(['codigo-reserva'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $login = $this->post('/login', [
            'email' => 'seguro@clubeinvestvida.com',
            'password' => 'Password!123',
        ]);

        $login->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        $code = (new Google2FA)->getCurrentOtp($secret);
        $challenge = $this->post('/two-factor-challenge', ['code' => $code]);

        $challenge->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($usuario);
        $this->assertTrue($usuario->fresh()->duas_etapas);
    }

    public function test_codigo_2fa_invalido_e_rejeitado(): void
    {
        $secret = app(TwoFactorAuthenticationProvider::class)->generateSecretKey();
        Usuario::factory()->create([
            'email' => 'seguro@clubeinvestvida.com',
            'senha_hash' => 'Password!123',
            'two_factor_secret' => Crypt::encrypt($secret),
            'two_factor_recovery_codes' => Crypt::encrypt(json_encode(['codigo-reserva'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post('/login', [
            'email' => 'seguro@clubeinvestvida.com',
            'password' => 'Password!123',
        ])->assertRedirect('/two-factor-challenge');

        $response = $this->post('/two-factor-challenge', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_dashboard_exige_autenticacao(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
