<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RamoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Vida', 'Previdência', 'Saúde', 'Viagem', 'Renda'] as $nome) {
            DB::table('ramos')->updateOrInsert(['nome' => $nome], ['grupo' => 'PESSOAS']);
        }
    }
}
