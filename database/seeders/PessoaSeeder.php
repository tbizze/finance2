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

        // Cria pessoas com base nos dados abaixo.
        $items = [
            [
                'nome_razao' => 'Centro de Cidadania SMP',
                'apelido_fantasia' => 'CSMP',
                'notas' => null,
                'codigo' => null,
                'cpf_cnpj' => '03488844000111',
                'rg_inscricao' => null,
                'ativo' => true,
            ],
            [
                'nome_razao' => 'Banco Santander S/A.',
                'apelido_fantasia' => 'Banco Santander 2194',
                'notas' => null,
                'codigo' => null,
                'cpf_cnpj' => '90400888081550',
                'rg_inscricao' => null,
                'ativo' => true,
            ],
            [
                'nome_razao' => 'Itaú Unibanco S/A.',
                'apelido_fantasia' => 'Banco Itaú 9182',
                'notas' => null,
                'codigo' => null,
                'cpf_cnpj' => '60701190338300',
                'rg_inscricao' => null,
                'ativo' => true,
            ],
            [
                'nome_razao' => 'Receita Federal',
                'apelido_fantasia' => 'Receita Federal',
                'notas' => null,
                'codigo' => null,
                'cpf_cnpj' => '',
                'rg_inscricao' => null,
                'ativo' => true,
            ],
        ];
        foreach ($items as $item) {
            Pessoa::create($item);
        }

        // Cria pessoas de forma randômica.
        Pessoa::factory(20)
        ->create()
        ->each(function ($pessoas) {
            $pessoa_grupos = collect(PessoaGrupo::pluck('id'));
            $pessoas->pessoaGrupos()->sync($pessoa_grupos->random());
        });
    }
}
