<?php

namespace Database\Seeders;

use App\Models\CtaMovimento;
use App\Models\Documento;
use App\Models\DocumentoBaixa;
use App\Models\DocumentoBaixaTipo;
use App\Models\DocumentoItem;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DocumentoBaixaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //$eventos = DocumentoBaixa::factory(20)->create();

        //$qde_baixas = 1500;
        //$qde_baixas = 300;
        $dados_fake = [];

        $documento_baixas = [];
        $documento_updates = [];
        $documento_id_selected = [];


        // Busca Documentos.
        $documentos = $this->getDocumentos();

        // Neste loop, monta array com dados fake.
        foreach ($documentos as $documento) {

            // Cria DocumentoBaixa Fake.
            $dados_fake = $this->getDadosNew($documento);

            // Se retorna dados de baixa, acrescenta no array '$documento_baixas'.
            if ($dados_fake) {
                // Coloca o documento_id dos dados criado em array '$documento_id_selected'.
                array_push($documento_id_selected, $dados_fake['documento_id']);
                // Coloca o DocumentoBaixa criado em array '$dados_fake'.
                array_push($documento_baixas, $dados_fake);
            }
        }
        //dd('pos docs', $documento_id_selected);

        // Neste loop, monta array com dados fake.
        /* for ($count = 1; $count <= $qde_baixas; $count++) {

            // Cria DocumentoBaixa.
            $dados_fake = $this->getDados($documento_id_selected);

            if ($dados_fake) {
                // Coloca o documento_id dos dados criado em array '$documento_id_selected'.
                array_push($documento_id_selected, $dados_fake['documento_id']);
                // Coloca o DocumentoBaixa criado em array '$dados_fake'.
                array_push($documento_baixas, $dados_fake);
            }
        } */
        //dd($documento_baixas);

        // Se houver dados fake na array '$documento_baixas'.
        if (count($documento_baixas) > 0) {

            // Abre uma transaction para salvar no BC os dados fake criado
            // Atualiza também os documentos em que 'status' seja diferente de QUITADO
            DB::transaction(function () use ($documento_baixas, $documento_updates) {

                foreach ($documento_baixas as $item) {
                    // Salva no BD documento criado.
                    DocumentoBaixa::create($item);

                    // Busca o Documento.
                    $documento = Documento::find($item['documento_id']);

                    // Atualiza status do documento para 1=>QUITADO.
                    if ($documento->documento_status_id != 1) {
                        $documento->update(['documento_status_id' => 1]);
                        //$docs_update = $docs_update . ', ' . $dados['documento_id'];
                    }
                    // Coloca 'id' do documento em array.
                    array_push($documento_updates, $item['documento_id']);
                }
            });
            // Array '$documento_baixas' está vazio.
        } else {
            dump('Nenhum documento criado');
        }
    }

    private function getDadosNew($documento)
    {
        $faker = Faker::create();

        //$ignore_ids = array_values(array_unique($documento_id_selected));

        // Busca Documento, passando ids pela variável '$ignore_ids' 
        // a ser ignorando, pois já foram selecionados.
        //$documento = $this->randomDocumento($ignore_ids);


        // Se retornou Documento, então cria dados Fake
        // Senão, retorna NULL.
        if ($documento) {

            // Soma total dos itens e define a data p/ baixa.
            $doc_items_sum = $this->getDocItemSum($documento->id);
            $data_venc = $documento->data_venc;

            //$x = $faker->dateTimeInInterval($startDate = '-7 days', $interval = '+ 5 days', $timezone = null) ;
            //$dt_baixa = $faker->dateTimeBetween('-5 month', 'now')->format('Y-m-d');
            $dt_baixa = $this->newData($data_venc);

            //dd($data_venc->format('Y-m-d'), $dt_baixa);

            switch ($documento->documento_tipo_id) {
                    //case 1: // a pagar
                    //case 2: // a receber

                case 3: // cta. paga
                    $documento_baixa_tipo_id = $this->ramdomBaixaTipo('D');
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id);
                    break;
                case 4: // cta. recebida
                    $documento_baixa_tipo_id = $this->ramdomBaixaTipo('C');
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id);
                    break;
                case 5: // tarifa e 
                case 6: // movimento titular
                    $documento_baixa_tipo_id = 10; // Débito em cta.
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id); // Débito em cta.
                    break;
            }

            $dados = [
                // 'dt_baixa','valor_baixa','dt_compensa','refer_baixa','cta_movimento_id','documento_id','documento_baixa_tipo_id'
                'dt_baixa' => $dt_baixa,
                'dt_compensa' => $dt_baixa,
                'valor_baixa' => $doc_items_sum,
                'refer_baixa' => null,
                'cta_movimento_id' => $cta_movimento_id,
                'documento_id' => $documento->id,
                'documento_baixa_tipo_id' => $documento_baixa_tipo_id,
            ];

            //dd($dados);
            return $dados;
        } else {
            // Como não encontrado Documento, retorna NULL.
            return null;
        }
    }

    private function getDados($documento_id_selected)
    {
        $faker = Faker::create();

        $ignore_ids = array_values(array_unique($documento_id_selected));

        // Busca Documento, passando ids pela variável '$ignore_ids' 
        // a ser ignorando, pois já foram selecionados.
        $documento = $this->randomDocumento($ignore_ids);


        // Se retornou Documento, então cria dados Fake
        // Senão, retorna NULL.
        if ($documento) {

            // Soma total dos itens e define a data p/ baixa.
            $doc_items_sum = $this->getDocItemSum($documento->id);
            $data_venc = $documento->data_venc;

            //$x = $faker->dateTimeInInterval($startDate = '-7 days', $interval = '+ 5 days', $timezone = null) ;
            //$dt_baixa = $faker->dateTimeBetween('-5 month', 'now')->format('Y-m-d');
            $dt_baixa = $this->newData($data_venc);

            //dd($dt_baixa);

            switch ($documento->documento_tipo_id) {
                    //case 1: // a pagar
                    //case 2: // a receber

                case 3: // cta. paga
                    $documento_baixa_tipo_id = $this->ramdomBaixaTipo('D');
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id);
                    break;
                case 4: // cta. recebida
                    $documento_baixa_tipo_id = $this->ramdomBaixaTipo('C');
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id);
                    break;
                case 5: // tarifa e 
                case 6: // movimento titular
                    $documento_baixa_tipo_id = 10; // Débito em cta.
                    $cta_movimento_id = $this->ramdomCtaMov($documento->pessoa_id, $documento_baixa_tipo_id); // Débito em cta.
                    break;
            }

            $dados = [
                // 'dt_baixa','valor_baixa','dt_compensa','refer_baixa','cta_movimento_id','documento_id','documento_baixa_tipo_id'
                'dt_baixa' => $dt_baixa,
                'dt_compensa' => $dt_baixa,
                'valor_baixa' => $doc_items_sum,
                'refer_baixa' => null,
                'cta_movimento_id' => $cta_movimento_id,
                'documento_id' => $documento->id,
                'documento_baixa_tipo_id' => $documento_baixa_tipo_id,
            ];

            //dd($dados);
            return $dados;
        } else {
            // Como não encontrado Documento, retorna NULL.
            return null;
        }
    }

    public function newData($value_date)
    {
        $faker = Faker::create();
        $new_date = $faker->randomElement([
            $value_date->addDays(15),
            $value_date->subDays(15),
            $value_date->addMonths(1),
            $value_date->addMonths(2),
            $value_date->addMonths(3),
            $value_date->subMonths(1),
            $value_date->subMonths(2),
            $value_date->subMonths(3),
        ]);

        $now = Carbon::now();
        //dump($new_date->diffInDays($now). ' | VENC: '.$new_date->format('d/m/Y') . ' | AGORA: '.$now->format('d/m/Y'));

        if ($new_date > $now) {
            //dump('>> data é maior: '.$new_date->format('d/m/Y'));
            return $now->toDateString();
        } else {
            //dump('<< é menor a data: '.$new_date->format('d/m/Y'));
            return $new_date->toDateString();
        }
        //dd('para');
    }

    public function newDataA($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
    private function ramdomCtaMov($pessoa_id, $baixa_tipo_id = null)
    {
        switch ($pessoa_id) {
            case 2: // Banco Santander
                $cta_movimento_id = 2;
                return $cta_movimento_id;

                break;
            case 3: // Banco Itaú
                $cta_movimento_id = 3;
                return $cta_movimento_id;

                break;
        }

        if ($baixa_tipo_id) {
            if ($baixa_tipo_id == 1 || $baixa_tipo_id == 6) {
                $cta_movimento_id = 1;
            } else {
                $cta_movimento_id = CtaMovimento::query()
                    ->whereNotIn('id', [1]) // negado a Conta Espécie.
                    ->get()
                    ->random(1)
                    ->pluck('id')
                    ->first();
            }
        }

        //dd($cta_movimento_id);
        return $cta_movimento_id;
    }

    private function getDocumentos()
    {
        try {
            //dd('get documento');
            $documentos = Documento::query()
                ->whereIn('documento_status_id', [1]) // somente status = pago.
                //->whereNotIn('id', $ignore_ids)
                ->whereIn('documento_tipo_id', [3, 4, 5, 6]) // comente tipo: pago | recebido | tarifa | movimento
                ->has('hasDocumentoItems') // contenha itens.
                ->doesnthave('hasDocumentoBaixa') // não contenha baixa.
                //->toSql();
                ->get()
                //->pluck('id')
            ;
            //dd($documentos);
        } catch (Exception $exception) {
            $message = $exception; //->getMessage();
            //dd($message);
            // InvalidArgumentException
            return null;

            /* if($exception instanceof ModelNotFoundException) {
                $message = 'User with ID: '.$request->user_id.' not found!';
            } */
        }
        //dd($documentos->toArray());
        //dd($documentos);
        return $documentos;
    }
    private function randomDocumento($ignore_ids)
    {

        try {
            $random = Documento::query()
                //->where('id', '>', 90)
                ->whereNotIn('id', $ignore_ids)
                ->whereIn('documento_tipo_id', [3, 4, 5, 6]) // pago | recebido | tarifa | movimento
                ->has('hasDocumentoItems')
                ->doesnthave('hasDocumentoBaixa')
                ->get()
                ->random(1)->first();
        } catch (Exception $exception) {
            $message = $exception; //->getMessage();
            //dd($message);
            // InvalidArgumentException
            return null;

            /* if($exception instanceof ModelNotFoundException) {
                $message = 'User with ID: '.$request->user_id.' not found!';
            } */
        }
        //dd($random->toArray());
        //dd($random);
        return $random;
    }
    private function getDocItemSum($documento_id)
    {
        $random = DocumentoItem::query()
            ->where('documento_id', $documento_id)
            ->sum('valor');
        return $random;
    }
    private function ramdomBaixaTipo($natureza)
    {
        $random = DocumentoBaixaTipo::query()
            ->where('natureza', $natureza)
            ->whereNotIn('id', [10])
            ->get()
            ->random(1)->pluck('id')->first();
        return $random;
    }
}
