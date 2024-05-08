<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\DocumentoItem;
use App\Models\PlanoCtaItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Faker\Factory as Faker;

class DocumentoItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        //$eventos = DocumentoItem::factory(150)->create();

        $faker = Faker::create();

        // Busca Documentos.
        $documentos = Documento::get();
        $dados_fake = [];

        foreach ($documentos as $documento) {
            // Define qde. de itens.
            switch ($documento->documento_classe_id) {
                case 2:  // NF-e
                case 3:  // Cupom fiscal
                case 10: // Recibo não fiscal
                    // TODO Restaurar conforme implementado = randomElement([1,2,3]).
                    //$qde_documento_items = $faker->randomElement([1,2,3]); // alterna entre esses valores.
                    $qde_documento_items = $faker->randomElement([1, 2, 3]); // alterna entre esses valores.
                    break;
                default:
                    $qde_documento_items = 1;
                    break;
            }

            for ($count = 1; $count <= $qde_documento_items; $count++) {
                // Cria DocumentoItens
                $documento_item = $this->makeDocumentoItem($documento);
                // Coloca o objeto DocumentoItem criado num array.
                array_push($dados_fake, $documento_item);
            }
        }

        //dd($dados_fake);

        if (count($dados_fake) > 0) {
            // Salva no BD os dados criado.
            foreach ($dados_fake as $item) {
                // Salva no BD o objeto array montado com os itens criados 
                DocumentoItem::create($item);
            }
        } else {
            dump('Nenhum DocumentoItem criado');
        }
    }

    private function makeDocumentoItem($documento)
    {
        $faker = Faker::create();

        switch ($documento->documento_classe_id) {
            case 9: // Tarifa
                $random_tarifa = $this->randomTarifa();
                $valor = $random_tarifa['valor'];
                $descricao = $random_tarifa['descricao'];
                $plano_cta = 30; // Tarifas bancárias.
                break;
            case 12: // Movimento titular
                $random_tarifa = $this->randomMovTitular();
                $valor = $random_tarifa['valor'];
                $descricao = $random_tarifa['descricao'];
                $plano_cta = 6; // Mov. mesmo titular.
                break;
            case 6: // GPS
                $valor = $faker->randomFloat(2, 3500, 5000);
                $descricao = 'Pgto. INSS retido';
                $plano_cta = 14; // INSS
                break;
            case 7: // DARF
                $valor = $faker->randomFloat(2, 150, 500);
                $descricao = 'Pgto. DARF';
                $plano_cta = 15; // DARF
                break;
            case 8: // Holerite/TRCT
                $valor = $faker->randomFloat(2, 35000, 43000);
                $descricao = 'Folha de Pgto.';
                $plano_cta = 9; // Salário
                break;
            case 11; // Recibo receitas
                $plano_cta = $this->randomPlanoCta($documento->documento_classe_id);

                if ($plano_cta == 42) {
                    $valor = $faker->randomElement([1420, 2400, 1550, 1950, 1750]);
                    $descricao = 'Doação familiar mensal';
                } elseif ($plano_cta == 43) {
                    $valor = $faker->randomElement([1420, 2400, 1550, 1950, 1750]);
                    $descricao = 'Doação familiar adicional';
                } elseif ($plano_cta == 49) {
                    $valor = $faker->randomFloat(2, 1590, 3500);
                    $descricao = 'Doação em produtos hort-fruti';
                } else {
                    $valor = $faker->randomFloat(2, 150, 2000);
                    $descricao = 'Doações ou eventos';
                }
                break;
            case 1: // NFS-e
                $valor = $faker->randomFloat(2, 500, 2500);
                $descricao = 'Serviço prestado';
                $plano_cta = $faker->randomElement([38, 39]);
                break;
            case 2: // NF-e
            case 3: // Cupom fiscal
                $valor = $faker->randomFloat(2, 50, 500);
                $descricao = $this->randomProdutos();
                $plano_cta = $this->randomPlanoCta($documento->documento_classe_id);
                break;
            case 4: // RPA
                $valor = $faker->randomElement([1420, 500]);
                $descricao = $faker->randomElement(['Pgto. serviços nutricionista', 'Pgto. serviços ed. física']);
                $plano_cta = 17; // Atendimento idosos
                break;
            case 5: // RPS
                $valor = $faker->randomElement([8, 12, 25, 40]);
                $descricao = 'Serviço de estacionamento';
                $plano_cta = 32;
                break;
            case 10: // Recibo não fiscal
                $valor = $faker->randomFloat(2, 30, 150);
                $descricao = $this->randomProdutos();
                $plano_cta = $this->randomPlanoCta($documento->documento_classe_id);
                break;
        }
        //dd('test 11-holerite');
        return [
            // 'descricao','notas','valor','documento_id','plano_cta_item_id'
            'descricao' => $descricao,
            'notas' => $faker->sentence(3),
            'valor' => $valor,
            'documento_id' => $documento->id,
            'plano_cta_item_id' => $plano_cta,
        ];
    }
    private function randomTarifa()
    {
        $items = [
            [
                'valor' => 5.50,
                'descricao' => 'Tarifa de emissão boleto'
            ],
            [
                'valor' => 0.89,
                'descricao' => 'Tarifa liquidação boleto'
            ],
            [
                'valor' => 9.50,
                'descricao' => 'Tarifa pgto pix'
            ],
            [
                'valor' => 1.55,
                'descricao' => 'Tarifa pgto pix'
            ]
        ];
        return collect($items)->random();
    }
    private function randomMovTitular()
    {
        $items = [
            [
                'valor' => 25000,
                'descricao' => 'Transf. p/ Folha Pgto.'
            ],
            [
                'valor' => 38000,
                'descricao' => 'Transf. p/ Folha Pgto.'
            ],
            [
                'valor' => 2900,
                'descricao' => 'Transf. p/ Pgto. Férias'
            ],
            [
                'valor' => 3500,
                'descricao' => 'Transf. p/ Pgto. Férias'
            ]
        ];
        return collect($items)->random();
    }
    private function randomProdutos()
    {
        $faker = Faker::create();

        return $faker->randomElement([
            'Roupas',
            'Calçados',
            'Eletrônicos',
            'Artigos de Higiene',
            'Artigos de Limpeza',
            'Eletrodomésticos',
            'Alimentos',
            'Medicamentos',
            'Cursos e Capacitações',
            'Consultorias',
            'Livros',
            'Artigos esportivos',
            'Bebidas',
            'Móveis',
            'Artigos para pets',
            'Itens de decoração',
            'Artigos de decoração',
            'Tapetes',
            'Quadros',
            'Aromatizador de ambientes',
            'Mantas para sofá',
            'Capas e almofadas',
            'Cortinas',
            'Vasos',
            'Objetos de decoração',
            'Espelhos',
            'Luminárias',
            'Porta-retratos',
        ]);
    }

    private function randomPlanoCta($documento_classe_id)
    {
        switch ($documento_classe_id) {
                // Casos diretos: 6-GPS, 7-DARF, 8-holerite, 9-tarifa, 12-movimento, 4-RPA, 5-RPS, 2-NFs
                // Casos Random: 11-recibo receitas // 1-NFe|3-NF Danf|10-Recibo  //

            case 11; // Recibo receitas
                $id = PlanoCtaItem::query()
                    ->where(function ($query) {
                        $query->where('codigo', 'like', '2.1%')
                            ->orWhere('codigo', 'like', '2.2%');
                    })
                    ->where('lcto', true)
                    ->get()->random(1)
                    ->pluck('id')->first();
                break;
            case 2:  // NF-e
            case 3:  // Cupom fiscal
            case 10: // Recibo não fiscal

                // Exclui ids de receitas: 
                $id = PlanoCtaItem::query()
                    ->where(function ($query) {
                        $query->where('codigo', 'like', '1.2%')
                            ->orWhere('codigo', 'like', '1.3.1%');
                    })
                    // Exclui esses ids: 9=> Salário | 14=> INSS | 15=>DARF | 30=>Tarifa | 6=>Movimento titular | 32-estacionamento | 17-Atend. idosos
                    //->whereNot('codigo', 'like', '2.1%')
                    ->whereNotIn('id', [9, 14, 15, 30, 6, 32, 17])
                    ->where('lcto', true)
                    ->get()->random(1)
                    ->pluck('id')->first();
                //dd('test', $id);
                break;
        }
        //dd('id',$documento_classe_id,$id);
        return $id;
    }
}
