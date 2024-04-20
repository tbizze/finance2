<?php

namespace Database\Seeders;

use App\Models\PessoaGrupo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PessoaGrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'nome' => 'Fornecedor',
                'notas' => 'Fornece mercadorias e produtos.'
            ],
            [
                'nome' => 'Prestador',
                'notas' => 'Presta serviços mediante NFs ou RPA.'
            ],
            [
                'nome' => 'Colaborador',
                'notas' => 'Funcionário CLT.'
            ],
            [
                'nome' => 'Benfeitor',
                'notas' => 'Presta benfeitorias doando quantias, materiais ou produtos.'
            ],
            [
                'nome' => 'Padrinho',
                'notas' => 'Doador de quantias recorrentes.'
            ],
            [
                'nome' => 'Voluntário',
                'notas' => 'Presta benfeitoria com serviço voluntário.'
            ],
            [
                'nome' => 'Associado',
                'notas' => 'Membro do CSMP.'
            ],
        ];

        foreach ($items as $item) {
            PessoaGrupo::create($item);
        }
    }
}
