<?php

namespace Database\Seeders;

use App\Models\DocumentoClasse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoClasseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'nome' => 'NFe',
                'order' => 1,
                'notas' => 'Nota fiscal paulista.'
            ],
            [
                'nome' => 'NFs',
                'order' => 2,
                'notas' => 'Nota fiscal de serviço eletrônica.'
            ],
            [
                'nome' => 'NF danf',
                'order' => 3,
                'notas' => 'Nota fiscal eletrônica.'
            ],
            [
                'nome' => 'RPA',
                'order' => 4,
                'notas' => 'Recibo de profissional autônomo.'
            ],
            [
                'nome' => 'RPS',
                'order' => 5,
                'notas' => 'Recibo provisório de serviços.'
            ],
            [
                'nome' => 'GPS',
                'order' => 6,
                'notas' => 'Guia p/ pgto. INSS.'
            ],
            [
                'nome' => 'DARF',
                'order' => 7,
                'notas' => 'Guia p/ pgto. DARF.'
            ],
        ];

        foreach ($items as $item) {
            DocumentoClasse::create($item);
        }
    }
}
