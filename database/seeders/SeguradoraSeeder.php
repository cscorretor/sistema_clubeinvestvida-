<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeguradoraSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bradesco Seguros', 'Icatu Seguros', 'MetLife', 'Porto', 'Prudential', 'SulAmérica', 'Zurich'] as $nome) {
            DB::table('seguradoras')->updateOrInsert(
                ['nome' => $nome],
                ['ativo' => true],
            );
        }
    }
}
