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
                'nome' => 'NFS-e',
                'order' => 1,
                'notas' => 'Nota fiscal de serviço eletrônica.'
            ],
            [
                'nome' => 'NF-e',
                'order' => 2,
                'notas' => 'Nota fiscal de produtos e mercadorias eletrônica.'
            ],
            [
                'nome' => 'Cupom Fiscal',
                'order' => 3,
                'notas' => 'Nota fiscal paulista.'
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
            [
                'nome' => 'Holerite/TRCT',
                'order' => 8,
                'notas' => 'Holerite e Termo Rescisão Contrato.'
            ],
            [
                'nome' => 'Tar. Banco',
                'order' => 9,
                'notas' => 'Taxas do banco debitadas na conta.'
            ],
            [
                'nome' => 'Recibo',
                'order' => 10,
                'notas' => 'Recibos NÃO FISCAIS.'
            ],
            [
                'nome' => 'Recibo receitas',
                'order' => 11,
                'notas' => 'Recibos ref. doações e contribuições recebidas.'
            ],
            [
                'nome' => 'Mov. Titular',
                'order' => 12,
                'notas' => 'Trans. entre contas mesmo titular.'
            ],
        ];

        foreach ($items as $item) {
            DocumentoClasse::create($item);
        }
    }
}
