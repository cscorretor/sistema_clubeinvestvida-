<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Administrador', 'Pâmela C. de Souza', 'Marco'] as $nome) {
            DB::table('produtores')->updateOrInsert(['nome' => $nome], ['ativo' => true]);
        }
    }
}
