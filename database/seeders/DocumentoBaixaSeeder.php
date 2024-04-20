<?php

namespace Database\Seeders;

use App\Models\CtaMovimento;
use App\Models\Documento;
use App\Models\DocumentoBaixa;
use App\Models\DocumentoBaixaTipo;
use App\Models\DocumentoItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DocumentoBaixaSeeder extends Seeder
{
    private $documento_id_used = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        //$eventos = DocumentoBaixa::factory(20)->create();

        $docs_update = '';
        for ($count = 1; $count <= 20; $count++) {
            $dados = $this->getDados();
            array_push($this->documento_id_used, $dados['documento_id']);
            
            DB::transaction(function() use ($dados, $docs_update) {

                // Cria DocumentoBaixa.
                DocumentoBaixa::create($dados);

                // Atualiza status do documento para 1=>QUITADO.
                $documento = Documento::find($dados['documento_id']);
                if($documento->documento_status_id != 1){
                    Documento::find($dados['documento_id'])
                        ->update(['documento_status_id' => 1]);
                    //$docs_update = $docs_update . ', ' . $dados['documento_id'];
                }
            });
        }
        //dd($this->documento_id_used, $dados['documento_id'],$docs_update);
    }

    private function getDados()
    {
        $faker = Faker::create();

        $doc = $this->getDocumento();
        $documento_id = $doc->id;  
        if ($documento_id) {

            $doc_items_sum = $this->getDocItem($documento_id);
            $dt_baixa = $faker->dateTimeBetween('-5 month', '+2 month')->format('Y-m-d');

            switch ($doc->documento_tipo_id) {
                case 1:
                case 3:
                case 5:
                    $documento_baixa_tipo_id = $this->getBaixaTipo('D');
                    break;
                case 2:
                case 4:
                    $documento_baixa_tipo_id = $this->getBaixaTipo('C');
                    break;
            }
            //dd($documento_id, $doc_items_sum, $documento_baixa_tipo_id);

            $dados = [
                // 'dt_baixa','valor_baixa','dt_compensa','refer_baixa','cta_movimento_id','documento_id','documento_baixa_tipo_id'
                'dt_baixa' => $dt_baixa,
                'dt_compensa' => $dt_baixa,
                'valor_baixa' => $doc_items_sum,
                'refer_baixa' => null,
                'cta_movimento_id' => $this->getRandom(CtaMovimento::class),
                'documento_id' => $documento_id,
                'documento_baixa_tipo_id' => $documento_baixa_tipo_id,
            ];

            return $dados;
        }else {
            return null;
        }
    }

    private function getRandom($model)
    {
        $random = $model::all()->random(1)->pluck('id'); 
        return $random[0];
    }
    private function getDocumento()
    {
        $random = Documento::query()
            //->where('id', 1000)
            ->whereIn('documento_tipo_id', [3,4,5])
            ->has('hasDocumentoItems')
            ->doesnthave('hasDocumentoBaixa')
            ->get()
            //->pluck('id')
            ->random(1)
            ;
            //dd($random->toArray());

        return $random[0];

    }
    private function getDocItem($documento_id)
    {
        $random = DocumentoItem::query()
            ->where('documento_id', $documento_id)
            ->sum('valor');
        return $random;
    }
    private function getBaixaTipo($documento_id)
    {
        $random = DocumentoBaixaTipo::query()
            ->where('natureza', $documento_id)
            ->get()
            ->random(1)
            ->pluck('id');
        return $random[0];
    }
}
