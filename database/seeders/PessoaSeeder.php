<?php

namespace Database\Seeders;

use App\Models\Pessoa;
use App\Models\PessoaGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PessoaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        //Pessoa::factory(10)->create();

        Pessoa::factory(20)
        ->create()
        ->each(function ($pessoas) {
            $pessoa_grupos = collect(PessoaGrupo::pluck('id'));
            $pessoas->pessoaGrupos()->sync($pessoa_grupos->random());
        });
    }
}
