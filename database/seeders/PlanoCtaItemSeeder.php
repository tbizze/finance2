<?php

namespace Database\Seeders;

use App\Models\PlanoCtaItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanoCtaItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $ctas_principais = [
            [
                'nome' => 'Despesas',
                'ativo' => true,
                'parent' => null,
                'codigo' => '1',
                'cod_externo' => null,
                'natureza' => 'D',
                'lcto' => false,
                'exibir_dre' => true,
                'plano_cta_id' => 1,
            ],
            [
                'nome' => 'Receitas',
                'ativo' => true,
                'parent' => null,
                'codigo' => '2',
                'cod_externo' => null,
                'natureza' => 'C',
                'lcto' => false,
                'exibir_dre' => true,
                'plano_cta_id' => 1,
            ],
        ];
        foreach ($ctas_principais as $item) {
            PlanoCtaItem::create($item);
        }

        //dd('teste');
        
        // --------- DADOS CTAS DESPESAS ------------------
        $grupo_debitos = [
            [
                'id' => '1.1',
                'name' => 'Pessoal e Autônomos',
            ],
            [
                'id' => '1.2',
                'name' => 'Operacionais',
            ],
            [
                'id' => '1.3',
                'name' => 'Adicionais',
            ],
        ];
        $grupo_Sub_debitos1 = [
            [
                'id' => '.1',
                'name' => 'Folha',
            ],
            [
                'id' => '.2',
                'name' => 'Impostos e Taxas',
            ],
            [
                'id' => '.3',
                'name' => 'Autônomos e terceiros',
            ],
        ];
        $grupo_Sub_debitos2 = [
            [
                'id' => '.1',
                'name' => 'Alimentação',
            ],
            [
                'id' => '.2',
                'name' => 'Higiene e limpeza',
            ],
            [
                'id' => '.3',
                'name' => 'Atend. Enfermagem',
            ],
        ];
        $grupo_Sub_debitos3 = [
            [
                'id' => '.1',
                'name' => 'Investimento',
            ],
            [
                'id' => '.2',
                'name' => 'Manutenção e conservação',
            ],
        ];


        $lcto_debitos11 = [
            [
                'id' => '.1',
                'name' => 'Salário',
            ],
            [
                'id' => '.2',
                'name' => 'Férias',
            ],
            [
                'id' => '.3',
                'name' => 'Hora Extra',
            ],
        ];
        $lcto_debitos12 = [
            [
                'id' => '.1',
                'name' => 'FGTS - Matriz',
            ],
            [
                'id' => '.2',
                'name' => 'INSS Retido - Matriz',
            ],
        ];
        $lcto_debitos13 = [
            [
                'id' => '.1',
                'name' => 'Atendimento Idosos',
            ],
        ];
        $lcto_debitos21 = [
            [
                'id' => '.1',
                'name' => 'Carnes',
            ],
            [
                'id' => '.2',
                'name' => 'Alimentos diversos',
            ],
            [
                'id' => '.3',
                'name' => 'Padaria',
            ],
        ];
        $lcto_debitos22 = [
            [
                'id' => '.1',
                'name' => 'Higiene',
            ],
            [
                'id' => '.2',
                'name' => 'Limpeza',
            ],
        ];
        $lcto_debitos23 = [
            [
                'id' => '.1',
                'name' => 'Insumos',
            ],
            [
                'id' => '.2',
                'name' => 'Medicamentos',
            ],
        ];
        $lcto_debitos31 = [
            [
                'id' => '.1',
                'name' => 'Aquisição Móv. Eqptos.',
            ],
            [
                'id' => '.2',
                'name' => 'Aquisição utensílhos',
            ],
        ];
        $lcto_debitos32 = [
            [
                'id' => '.1',
                'name' => 'Manut. Autom. - mão obra',
            ],
            [
                'id' => '.2',
                'name' => 'Manut. Predial - mão obra',
            ],
        ];

        //dd($grupo_debitos,$grupo_Sub_debitos1);

        foreach ($grupo_debitos as $grupo_debito) {
            $dados1 = [
                'nome' => $grupo_debito['name'],
                //'notas' => $this->faker->sentence(3),
                'ativo' => true,
                'parent' => 1,
                'codigo' => $grupo_debito['id'],
                'cod_externo' => null,
                'natureza' => 'D',
                'lcto' => false,
                'exibir_dre' => true,
                'plano_cta_id' => 1,
            ];
            //dd($dados1);
            $record = PlanoCtaItem::create($dados1);
            if ($grupo_debito['id'] == '1.1'){
                foreach ($grupo_Sub_debitos1 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'D',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_debitos11 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.2'){
                        foreach ($lcto_debitos12 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.3'){
                        foreach ($lcto_debitos13 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
            if ($grupo_debito['id'] == '1.2'){
                foreach ($grupo_Sub_debitos2 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'D',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_debitos21 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.2'){
                        foreach ($lcto_debitos22 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.3'){
                        foreach ($lcto_debitos23 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
            if ($grupo_debito['id'] == '1.3'){
                foreach ($grupo_Sub_debitos3 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'D',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_debitos31 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.2'){
                        foreach ($lcto_debitos32 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'D',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
        }


        // --------- DADOS CTAS RECEITAS ------------------
        $grupo_creditos = [
            [
                'id' => '2.1',
                'name' => 'Receitas',
            ],
            [
                'id' => '2.2',
                'name' => 'Créditos',
            ],
            [
                'id' => '2.3',
                'name' => 'Externos',
            ],
        ];
        $grupo_Sub_creditos1 = [
            [
                'id' => '.1',
                'name' => 'Familiar',
            ],
            [
                'id' => '.2',
                'name' => 'Eventos em receitas',
            ],
            [
                'id' => '.3',
                'name' => 'Benfeitores',
            ],
        ];
        $grupo_Sub_creditos2 = [
            [
                'id' => '.1',
                'name' => 'Crédito',
            ],
            [
                'id' => '.2',
                'name' => 'Rendimentos',
            ],
        ];
        $grupo_Sub_creditos3 = [
            [
                'id' => '.1',
                'name' => 'Repasse filiais',
            ],
        ];
        
        $lcto_credito11 = [
            [
                'id' => '.1',
                'name' => 'Doação familiar 70%',
            ],
            [
                'id' => '.2',
                'name' => 'Doação familiar 13º',
            ],
        ];
        $lcto_credito12 = [
            [
                'id' => '.1',
                'name' => 'Receitas c/ bazar',
            ],
            [
                'id' => '.2',
                'name' => 'Receitas c/ eventos',
            ],
        ];
        $lcto_credito13 = [
            [
                'id' => '.1',
                'name' => 'Doações regulares',
            ],
            [
                'id' => '.2',
                'name' => 'Doação hortifruti',
            ],
        ];
        $lcto_credito21 = [
            [
                'id' => '.1',
                'name' => 'Crédito NF Paulista',
            ],
        ];
        $lcto_credito22 = [
            [
                'id' => '.1',
                'name' => 'Rendimentos aplicações',
            ],
        ];
        $lcto_credito31 = [
            [
                'id' => '.1',
                'name' => 'Repasses de filiais',
            ],
        ];

        foreach ($grupo_creditos as $grupo_credito) {
            $dados1 = [
                'nome' => $grupo_credito['name'],
                //'notas' => $this->faker->sentence(3),
                'ativo' => true,
                'parent' => 2,
                'codigo' => $grupo_credito['id'],
                'cod_externo' => null,
                'natureza' => 'C',
                'lcto' => false,
                'exibir_dre' => true,
                'plano_cta_id' => 1,
            ];
            //dd($dados1);
            $record = PlanoCtaItem::create($dados1);
            if ($grupo_credito['id'] == '2.1'){
                foreach ($grupo_Sub_creditos1 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'C',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_credito11 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.2'){
                        foreach ($lcto_credito12 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.3'){
                        foreach ($lcto_credito13 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
            if ($grupo_credito['id'] == '2.2'){
                foreach ($grupo_Sub_creditos2 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'C',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_credito21 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                    if ($item_sub['id'] == '.2'){
                        foreach ($lcto_credito22 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
            if ($grupo_credito['id'] == '2.3'){
                foreach ($grupo_Sub_creditos3 as $item_sub) {
                    $dados = [
                        'nome' => $item_sub['name'],
                        //'notas' => $this->faker->sentence(3),
                        'ativo' => true,
                        'parent' => $record->id,
                        'codigo' => $record->codigo . $item_sub['id'],
                        'cod_externo' => null,
                        'natureza' => 'C',
                        'lcto' => false,
                        'exibir_dre' => true,
                        'plano_cta_id' => 1,
                    ];
                    $record_sub = PlanoCtaItem::create($dados);
                    if ($item_sub['id'] == '.1'){
                        foreach ($lcto_credito31 as $item_sub_lcto) {
                            $dados = [
                                'nome' => $item_sub_lcto['name'],
                                //'notas' => $this->faker->sentence(3),
                                'ativo' => true,
                                'parent' => $record_sub->id,
                                'codigo' => $record_sub->codigo . $item_sub_lcto['id'],
                                'cod_externo' => null,
                                'natureza' => 'C',
                                'lcto' => true,
                                'exibir_dre' => true,
                                'plano_cta_id' => 1,
                            ];
                            PlanoCtaItem::create($dados);
                        }
                    }
                }
            }
        }
    }
}
