<?php

namespace Database\Seeders;

use App\Models\DocumentoStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'nome' => 'Quitado',
                'notas' => 'Documento recebido/pago.'
            ],
            [
                'nome' => 'No prazo',
                'notas' => 'Documento a vencer.'
            ],
            [
                'nome' => 'Vencido',
                'notas' => 'Documento em atraso.'
            ],
            [
                'nome' => 'Cancelado',
                'notas' => 'Documento cancelado.'
            ],
        ];

        foreach ($items as $item) {
            DocumentoStatus::create($item);
        }
    }
}
