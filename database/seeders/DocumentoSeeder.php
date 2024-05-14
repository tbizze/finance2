<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\DocumentoClasse;
use App\Models\DocumentoItem;
use App\Models\Pessoa;
use App\Models\PlanoCtaItem;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class DocumentoSeeder extends Seeder
{
    public $data_inicio = '';
    public $data_fim = '';
    /**
     * Run the database seeds.
     * 
     * 
     */

    public function run(): void
    {
        //
        //$eventos = Documento::factory(100)->create();
        // TODO: Restaurar conforme implementado. qde_documentos e data_início.
        $qde_documentos = 3000; // Qde de documentos a criar.
        $qde_documento_tipos = 6; // Qde de documentos a criar.
        $this->data_inicio = '2023-01-01';  // Faz calculo a partir da data atual: '-5 month' / '-2 year' / '-5 days'...  /// Data exata '2022-01-01'.
        $this->data_fim = '2023-11-30';  // Faz calculo a partir da data atual: '+3 month' / '+2 year' / '+5 days'...  /// Data exata '2024-12-31'.

        $divisor_limit = (int)floor($qde_documentos / $qde_documento_tipos); // Esse valor irá definir a qde. de documentos por tipo.

        $count_divisor_limit = 1;
        $documento_tipo_id = 1;
        $documentos = [];

        for ($count = 1; $count <= $qde_documentos; $count++) {

            // Cria Documento.
            $dados_fake = $this->makeDocumento($documento_tipo_id);

            if ($dados_fake) {
                // Coloca o DocumentoBaixa criado em array '$dados_fake'.
                array_push($documentos, $dados_fake);
            }

            // Toda vez que chegar o 'count_divisor_limit' atingir o divisor:
            // - soma mais 1 ao 'documento_tipo_id'
            // - retorna o 'count_divisor_limit' p/ início.
            if ($count_divisor_limit == $divisor_limit) {
                // Avança p/ próximo 'documento_tipo_id'.
                $documento_tipo_id++;
                // Retorna o divisor p/ 1.
                $count_divisor_limit = 1;
            } else {
                // soma ao multiplicante.
                $count_divisor_limit++;
            }

            // Limita o documento_id no número total de DocumentoTipo = 6.
            if ($documento_tipo_id > 6) {
                $documento_tipo_id = 1;
            }
        }

        if (count($documentos) > 0) {
            // Abre uma transaction para salvar no BC os dados fake criado
            // Atualiza também os documentos em que 'status' seja diferente de QUITADO
            DB::transaction(function () use ($documentos) {
                foreach ($documentos as $item) {
                    // Salva no BD documento criado.
                    Documento::create($item);
                }
            });
            //dd('Documentos criados com sucesso!');
        } else {
            dump('Nenhum documento criado');
        }
    }

    private function makeDocumento($Documento_tipo_id)
    {
        $faker = Faker::create();

        switch ($Documento_tipo_id) {
            case 5: // Tar. bancária
                $documento_classe_id = 9;
                // TODO: Retornar como antes: endData = 'now'.
                //$data_venc = $faker->dateTimeBetween($this->data_inicio, 'now')->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $status_id = 1; // quitado
                $pessoa_id = $faker->randomElement([2, 3]); // Banco Santander ou Banco Itaú

                break;
            case 6: // Mesmo. titular
                $documento_classe_id = 12;
                // TODO: Retornar como antes: endData = 'now'.
                //$data_venc = $faker->dateTimeBetween($this->data_inicio, 'now')->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $status_id = 1; // quitado
                $pessoa_id = 1; // Centro de Cidadania

                break;
            case 3: // Pago
                $documento_classe_id = $this->randomDocumentoClasse([9, 11, 12]); // 9-tarifa, 11-recibo recebimento, 12-Mesmo titular
                // TODO: Retornar como antes: endData = 'now'.
                //$data_venc = $faker->dateTimeBetween($this->data_inicio, 'now')->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $status_id = 1; // quitado
                $pessoa_id = $this->randomPessoa([1, 2, 3, 4], $documento_classe_id);
                break;
            case 4: // Recebido
                $documento_classe_id = $this->randomDocumentoClasse([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12]); // fica só o 11-recibo recebimento
                // TODO: Retornar como antes: endData = 'now'.
                //$data_venc = $faker->dateTimeBetween($this->data_inicio, 'now')->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // como já quitado, data anterior até 'now'.
                $status_id = 1; // quitado
                $pessoa_id = $this->randomPessoa([1, 2, 3, 4], $documento_classe_id);

                break;
            case 1: // A pagar
                $documento_classe_id = $this->randomDocumentoClasse([9, 11, 12]); // 9-tarifa, 11-recibo recebimento, 12-Mesmo titular
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // pode estar vencido ou a vencer, data anterior até  data fim.
                $status_id = $this->randomStatus($data_venc); // =======> $status_id = 2,3,4;
                $pessoa_id = $this->randomPessoa([1, 2, 3, 4], $documento_classe_id);

                break;
            case 2: // A receber
                $documento_classe_id = $this->randomDocumentoClasse([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12]); // fica só o 11-recibo recebimento
                $data_venc = $faker->dateTimeBetween($this->data_inicio, $this->data_fim)->format('Y-m-d'); // pode estar vencido ou a vencer, data anterior até  data fim.
                $status_id = $this->randomStatus($data_venc); // =======> $status_id = 2,3,4;
                $pessoa_id = $this->randomPessoa([1, 2, 3, 4], $documento_classe_id);

                break;
        }
        //dump('dt_venc:'.$data_venc . ' || doc_tipo_id:'.$Documento_tipo_id);

        try {
            $dados =  [
                // 'data_emissao','data_venc','notas','codigo','condicao','parcela','documento_tipo_id','documento_classe_id','pessoa_id','status_id'

                'data_emissao' => $data_venc,
                'data_venc' => $data_venc,
                'notas' => $faker->sentence(2),
                'codigo' => $faker->randomNumber(4),
                'condicao' => 1,
                'parcela' => 0,
                'documento_tipo_id' => $Documento_tipo_id,
                'documento_classe_id' => $documento_classe_id,
                'documento_status_id' => $status_id,
                'pessoa_id' => $pessoa_id,
            ];
            //dd($dados);
            return $dados;
        } catch (Exception $exception) {
            $message = $exception; //->getMessage();
            //$message = $exception->getMessage();
            return false;
        }
    }
    private function randomStatus($data_venc)
    {
        $now = date('Y-m-d');
        if ($data_venc > $now) {
            return 2; // 2 => NO PRAZO
        } else {
            return 3; // 3 => VENCIDO
        }
    }
    private function getRandom($model)
    {
        $random = $model::all()->random(1)->pluck('id');
        return $random[0];
    }
    private function randomPessoa($ids_not, $classe_id)
    {
        if ($classe_id == 6 || $classe_id == 7) {
            return 4;
        }
        $random = Pessoa::query($ids_not)
            ->whereNotIn('id', $ids_not)
            ->get()->random(1)
            ->pluck('id')->first();
        return $random;
    }
    private function randomDocumentoClasse($ids_not)
    {
        $random = DocumentoClasse::query()
            ->whereNotIn('id', $ids_not)
            ->get()->random(1)->pluck('id');
        return $random[0];
    }
}
