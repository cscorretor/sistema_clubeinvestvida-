<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Usuario;
use App\Policies\ClientePolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (Usuario $usuario) => $usuario->isAdmin() ? true : null);
        Gate::policy(Cliente::class, ClientePolicy::class);

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof Usuario) {
                $event->user->forceFill(['ultimo_acesso' => now()])->saveQuietly();
            }
        });
    }
}
