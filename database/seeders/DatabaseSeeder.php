<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Roda um conjunto de Seeder.
        $this->call([

            //PermissionSeeder::class,
            //UserSeeder::class,

            /* DocumentoBaixaTipoSeeder::class,
            DocumentoClasseSeeder::class,
            DocumentoStatusSeeder::class,
            DocumentoTipoSeeder::class,
            PessoaGrupoSeeder::class, 

            PlanoCtaSeeder::class,
            PlanoCtaItemSeeder::class,

            PessoaSeeder::class,
            CtaMovimentoSeeder::class,  */

            // DocumentoSeeder::class,
            // DocumentoItemSeeder::class,
            // DocumentoBaixaSeeder::class,

            JobcardSeeder::class,

        ]);
    }
}
