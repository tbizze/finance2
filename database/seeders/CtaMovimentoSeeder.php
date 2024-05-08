<?php

namespace Database\Seeders;

use App\Models\CtaMovimento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CtaMovimentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 'nome','agencia','numero','descricao','cod_externo','notas','ativo'
        $items = [
            [
                'nome' => 'Espécie',
                'descricao' => 'Espécie',
                'notas' => 'Cta. p/ movimento em espécie.',
                'agencia' => null,
                'numero' => null,
                'cod_externo' => null,
                'ativo' => true,
            ],
            [
                'nome' => 'Santander SA',
                'descricao' => 'Santander',
                'notas' => 'Cta. bancária.',
                'agencia' => '2194',
                'numero' => '13001058-4',
                'cod_externo' => null,
                'ativo' => true,
            ],
            [
                'nome' => 'Itaú Unibanco S/A',
                'descricao' => 'Itau',
                'notas' => 'Cta. bancária.',
                'agencia' => '9182',
                'numero' => '0207-9',
                'cod_externo' => null,
                'ativo' => true,
            ],
        ];

        foreach ($items as $item) {
            CtaMovimento::create($item);
        }
    }
}
