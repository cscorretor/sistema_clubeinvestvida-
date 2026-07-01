<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::twoFactorChallengeView(fn () => view('auth.two-factor-challenge'));

        Fortify::authenticateUsing(function (Request $request): ?Usuario {
            $usuario = Usuario::query()
                ->where('email', Str::lower(trim((string) $request->input('email'))))
                ->where('ativo', true)
                ->first();

            if ($usuario && Hash::check((string) $request->input('password'), $usuario->senha_hash)) {
                return $usuario;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::transliterate(Str::lower((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('two-factor', fn (Request $request): Limit => Limit::perMinute(5)
            ->by((string) $request->session()->get('login.id', $request->ip())));
    }
}
