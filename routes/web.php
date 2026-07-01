<?php

use App\Models\Cliente;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/acesso-administrativo', fn () => response()->noContent())
        ->middleware('profile:ADMIN')
        ->name('admin.access');

    Route::get('/controle-clientes', function () {
        Gate::authorize('viewAny', Cliente::class);

        return response()->noContent();
    })->name('clientes.access');
});
