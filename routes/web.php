<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfissaoController;
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

    Route::get('/clientes', [ClienteController::class, 'index'])
        ->name('clientes.index');

    Route::get('/clientes/novo', [ClienteController::class, 'create'])
        ->can('create', Cliente::class)
        ->name('clientes.create');

    Route::post('/clientes', [ClienteController::class, 'store'])
        ->can('create', Cliente::class)
        ->name('clientes.store');

    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])
        ->whereNumber('cliente')
        ->name('clientes.edit');

    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])
        ->whereNumber('cliente')
        ->name('clientes.update');

    Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])
        ->whereNumber('cliente')
        ->name('clientes.show');

    Route::get('/api/profissoes', [ProfissaoController::class, 'index'])
        ->name('api.profissoes.index');
});
