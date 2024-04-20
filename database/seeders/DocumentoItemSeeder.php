<?php

namespace Database\Seeders;

use App\Models\DocumentoItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $eventos = DocumentoItem::factory(150)->create();
    }
}
