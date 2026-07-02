<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RamoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Vida' => 'PESSOAS',
            'Previdência' => 'PESSOAS',
            'Saúde' => 'PESSOAS',
            'Viagem' => 'PESSOAS',
            'Renda' => 'PESSOAS',
            'Residencial' => 'PATRIMONIAL',
        ] as $nome => $grupo) {
            DB::table('ramos')->updateOrInsert(['nome' => $nome], ['grupo' => $grupo]);
        }
    }
}
