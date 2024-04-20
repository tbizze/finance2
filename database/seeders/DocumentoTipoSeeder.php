<?php

namespace Database\Seeders;

use App\Models\DocumentoTipo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'nome' => 'Cta. a pagar',
                'notas' => 'Previsão de conta a pagar.'
            ],
            [
                'nome' => 'Cta. a receber',
                'notas' => 'Previsão de conta a receber.'
            ],
            [
                'nome' => 'Cta. paga',
                'notas' => 'Realizado, conta paga.'
            ],
            [
                'nome' => 'Cta. recebida',
                'notas' => 'Realizado, conta recebida.'
            ],
            [
                'nome' => 'Desp. bancária',
                'notas' => 'Tarifas e despesas com banco.'
            ],
            [
                'nome' => 'Mov. entre ctas.',
                'notas' => 'Movimento entre contas do mesmo titular.'
            ],
        ];

        foreach ($items as $item) {
            DocumentoTipo::create($item);
        }
    }
}
