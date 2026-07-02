<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apolices', function (Blueprint $table): void {
            $table->json('dados_produto')->nullable()->after('capital_segurado');
        });
    }

    public function down(): void
    {
        Schema::table('apolices', function (Blueprint $table): void {
            $table->dropColumn('dados_produto');
        });
    }
};
