<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProdutorSeeder::class,
            RamoSeeder::class,
            ProfissaoSeeder::class,
            AdminUsuarioSeeder::class,
        ]);
    }
}
