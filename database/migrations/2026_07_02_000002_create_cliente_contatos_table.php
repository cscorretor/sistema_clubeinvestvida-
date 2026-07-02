<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_contatos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->boolean('principal')->default(false);
            $table->string('nome', 150);
            $table->string('cargo', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->foreign('cliente_id', 'fk_contato_cliente')
                ->references('id')
                ->on('clientes')
                ->cascadeOnDelete();
            $table->index('cliente_id', 'idx_contato_cliente');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_contatos');
    }
};
