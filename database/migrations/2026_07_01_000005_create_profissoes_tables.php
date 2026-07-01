<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profissoes', function (Blueprint $table) {
            $table->id();
            $table->char('codigo_cbo', 6)->unique();
            $table->string('titulo', 180);
            $table->string('titulo_busca', 180)->index();
        });

        Schema::create('profissao_sinonimos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissao_id')->constrained('profissoes')->cascadeOnDelete();
            $table->string('titulo', 180);
            $table->string('titulo_busca', 180)->index();
            $table->unique(['profissao_id', 'titulo_busca'], 'uq_profissao_sinonimo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profissao_sinonimos');
        Schema::dropIfExists('profissoes');
    }
};
