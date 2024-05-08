<?php

namespace Database\Seeders;

use App\Models\DocumentoBaixaTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoBaixaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            // --- RECEITAS
            [
                'nome' => 'Espécie',
                'natureza' => 'C',
                'notas' => 'Recebimento em espécie.'
            ],
            [
                'nome' => 'Depósito',
                'natureza' => 'C',
                'notas' => 'Recebimento em depósito.'
            ],
            [
                'nome' => 'Transferência',
                'natureza' => 'C',
                'notas' => 'Recebimento em DOC/TED/PIX.'
            ],
            [
                'nome' => 'Boleto',
                'natureza' => 'C',
                'notas' => 'Recebimento em boleto.'
            ],
            [
                'nome' => 'Cartão',
                'natureza' => 'C',
                'notas' => 'Recebimento em cartão.'
            ],
            
            // --- DESPESAS
            [
                'nome' => 'Espécie',
                'natureza' => 'D',
                'notas' => 'Pagamento em espécie.'
            ],
            [
                'nome' => 'Depósito',
                'natureza' => 'D',
                'notas' => 'Pagamento em depósito.'
            ],
            [
                'nome' => 'Transferência',
                'natureza' => 'D',
                'notas' => 'Pagamento em DOC/TED/PIX.'
            ],
            [
                'nome' => 'Internet Banking',
                'natureza' => 'D',
                'notas' => 'Pagamento em banco digital.'
            ],
            [
                'nome' => 'Débito em cta.',
                'natureza' => 'D',
                'notas' => 'Pagamento debitado direto na conta.'
            ],
        ];

        foreach ($items as $item) {
            DocumentoBaixaTipo::create($item);
        }
    }
}
