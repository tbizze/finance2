<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoBaixa;
use App\Models\DocumentoClasse;
use App\Models\DocumentoItem;
use App\Models\DocumentoTipo;
use App\Models\Pessoa;
use App\Models\PlanoCtaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeTestController extends Controller
{
    public function confBaixas(Request $request)
    {
        // #########################################
        // Prepara filtros, com base na URL
        $cta_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('cta_id')) {
            $cta_id = (int)$request->query('cta_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        // #########################################
        // Busca itens no BD
        $itens_resumo = DocumentoBaixa::query()
            //->select('documento_id')
            //->selectRaw('COUNT(documento_baixas.id) as itens_contado, SUM(valor_baixa) as valor_somado')
            ->selectRaw('SUM(valor_baixa) as valor_somado')
            ->selectRaw('YEAR(dt_baixa) as year')
            //->join('plano_cta_items', 'documento_items.plano_cta_item_id', '=', 'plano_cta_items.id')

            ->when($ano_id, function ($query, $value) {
                $query->whereYear('dt_baixa', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
            })

            ->groupByRaw('YEAR(dt_baixa)')
            //->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->get();

        //dd($itens_resumo);
        foreach ($itens_resumo as $item) {
            isset($mes_id) ? $periodo = 'ANO: ' . $item->year . '/' . $mes_id : $periodo = 'ANO: ' . $item->year;
            echo $periodo . ' ||| TOTAL: ' . $this->formatNumber($item->valor_somado) . '<br>';
        }
    }
    public function confDocs(Request $request)
    {
        // #########################################
        // Prepara filtros, com base na URL
        $cta_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('cta_id')) {
            $cta_id = (int)$request->query('cta_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        // Documento
        $itens_resumo = Documento::query()
            ->selectRaw('COUNT(documentos.id) as itens_contado')
            // Wheres
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('data_venc', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('data_venc', $value); // Filtra pelo ano
            })
            ->where('documento_status_id', 1) // Filtra: somente pago.
            // GroupBy
            ->groupByRaw('YEAR(data_venc)')->get();
        echo 'Documentos: ';
        dump($itens_resumo->toArray());

        // Documento 2: relaciones
        $itens_resumo = Documento::query()
            ->selectRaw('COUNT(documentos.id) as itens_contado')
            ->selectRaw('SUM(documento_baixas.dt_baixa) as valor_somado')
            ->join('documento_baixas', 'documento_baixas.documento_id', '=', 'documentos.id')
            // Wheres
            // Com o código abaixo, vai trazer aquele que é de um ano e foi baixado ano seguinte ==> 406.;
            /* ->whereHas('hasDocumentoBaixa', function ($query) use ($mes_id, $ano_id){
            $query->when($ano_id, function ($query, $value) {
                $query->whereYear('dt_baixa', $value); // Filtra pelo mês
            });
            $query->when($mes_id, function ($query, $value) {
                $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
            });
        }) */
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('data_venc', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('data_venc', $value); // Filtra pelo ano
            })
            ->where('documento_status_id', 1) // Filtra: somente pago.
            // GroupBy
            ->groupByRaw('YEAR(data_venc)')->get();
        echo 'Documentos relacionado c/ Baixas';
        dump($itens_resumo->toArray());
        //dump($itens_resumo);

        // Documento 3: listado
        $itens_resumo = Documento::query()
            //->with('hasDocumentoBaixa:id,dt_baixa')
            ->select('id', 'data_venc', 'documento_tipo_id', 'documento_status_id')
            ->withAggregate('hasDocumentoBaixa', 'dt_baixa')
            ->withAggregate('hasDocumentoBaixa', 'valor_baixa')
            //->whereHas('hasDocumentoBaixa')
            //->where('documento_status_id', 1) // Filtra: somente pago.

            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('data_venc', $value); // Filtra pelo ano
            })
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('data_venc', $value); // Filtra pelo ano
            })
            //->toSql();
            ->get();

        //echo 'Documentos: listado';
        //dump($itens_resumo);
        //dump($itens_resumo->toArray());



        // #########################################
        // DocumentoBaixa
        $itens_resumo = DocumentoBaixa::query()
            ->selectRaw('COUNT(id) as itens_contado')
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('dt_baixa', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
            })
            ->groupByRaw('YEAR(dt_baixa)')
            //->toSql();
            ->get();
        echo 'Documentos Baixa';
        //dump($itens_resumo);
        dump($itens_resumo->toArray());

        // #########################################
        // DocumentoItem
        $itens_resumo = DocumentoItem::query()
            ->selectRaw('COUNT(documento_items.id) as itens_contado')
            ->join('documentos', 'documento_items.documento_id', '=', 'documentos.id')
            ->whereHas('toDocumento', function ($query) use ($mes_id, $ano_id) {
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('data_venc', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('data_venc', $value); // Filtra pelo ano
                });
            })
            ->groupByRaw('YEAR(documentos.data_venc)')->get();
        echo 'Documentos Itens';
        dump($itens_resumo->toArray());

        // DocumentoItem 2: listado
        $itens_resumo = DocumentoItem::query()
            //->with('hasDocumentoBaixa:id,dt_baixa')
            ->select('id', 'descricao', 'documento_id', 'plano_cta_item_id', 'valor')
            ->withAggregate('toDocumento', 'data_venc')
            //->withAggregate('hasDocumentoBaixa','valor_baixa')
            ->whereHas('toDocumento', function ($query) use ($mes_id, $ano_id) {
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('data_venc', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('data_venc', $value); // Filtra pelo ano
                });
            })
            //->where('documento_status_id', 1) // Filtra: somente pago.

            ->toSql();
        //->get();
        echo 'Documentos Itens: listado';
        dump($itens_resumo);
        //dump($itens_resumo->toArray());






        // #########################################
        // Busca itens no BD
        $itens_resumo = Documento::query()
            //->select('documento_id')
            //->selectRaw('COUNT(documento_baixas.id) as itens_contado, SUM(valor_baixa) as valor_somado')
            ->selectRaw('SUM(documento_items.valor) as valor_somado, COUNT(documentos.id) as itens_contado')
            ->selectRaw('YEAR(data_venc) as year')
            ->join('documento_items', 'documento_items.documento_id', '=', 'documentos.id')

            ->when($ano_id, function ($query, $value) {
                $query->whereYear('data_venc', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('data_venc', $value); // Filtra pelo ano
            })

            ->groupByRaw('YEAR(data_venc)')
            //->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->get();

        //dd($itens_resumo);
        foreach ($itens_resumo as $item) {
            isset($mes_id) ? $periodo = 'ANO: ' . $item->year . '/' . $mes_id : $periodo = 'ANO: ' . $item->year;
            echo $periodo . ' ||| ITENS : ' . $item->itens_contado . ' ||| TOTAL: ' . $this->formatNumber($item->valor_somado) . '<br>';
        }
    }




    // Busca Contas a Pagar e a Receber
    public function previsao(Request $request)
    {
        $tipo_id = (int)$request->route('tipo_id');

        $classe_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('classe_id')) {
            $classe_id = (int)$request->query('classe_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        // monta consulta.
        $documentos = Documento::query()
            ->with(['toPessoa:id,nome_razao,cpf_cnpj'])
            ->with(['toDocumentoStatus:id,nome', 'toDocumentoClasse:id,nome', 'toDocumentoTipo:id,nome'])
            ->withSum(['hasDocumentoItems'], 'valor')
            ->withCount(['hasDocumentoItems'], 'id')

            ->where('documento_tipo_id', $tipo_id)
            ->when($classe_id, function ($query, $val) {
                $query->where('documento_classe_id', $val);
            })
            ->when($mes_id, function ($query, $val) {
                $query->whereMonth('data_venc', $val);
            })
            ->when($ano_id, function ($query, $val) {
                $query->whereYear('data_venc', $val);
            })
            ->get();

        // Obtém TOTAIS.
        $total_linhas = $documentos->count();
        $valor_total = $this->formatNumber($documentos->sum('has_documento_items_sum_valor'));

        $documento_classes = DocumentoClasse::get(['id', 'nome']);
        $documento_tipos = DocumentoTipo::get(['id', 'nome']);

        //dd(isset($mes_id) ? 'mes='.$mes_id : '');

        //dd($documentos->toArray());
        //dd($request->has($request->route('classe_id')));
        return view('test-prev', compact([
            'documentos',
            'documento_classes',
            'documento_tipos',
            'total_linhas',
            'valor_total',
            'tipo_id', 'classe_id', 'mes_id', 'ano_id'
        ]));
    }

    // Busca Contas Pagas, Recebidas, Tarifas e Transferências.
    public function real(Request $request)
    {
        $tipo_id = (int)$request->route('tipo_id');

        $classe_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('classe_id')) {
            $classe_id = (int)$request->query('classe_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        //dd($tipo_id, $classe_id, $mes_id, $request->query());
        $date = '';

        // monta consulta.
        $documento_baixas = DocumentoBaixa::query()
            ->with(['toDocumento.toPessoa:id,nome_razao,cpf_cnpj'])
            ->with(['toDocumento', 'toCtaMovimento', 'toDocumentoBaixaTipo'])
            ->with(['toDocumento.toDocumentoStatus:id,nome', 'toDocumento.toDocumentoClasse:id,nome', 'toDocumento.toDocumentoTipo:id,nome'])
            //->withSum(['hasDocumentoItems'], 'valor')
            //->withCount(['hasDocumentoItems'], 'id')

            // Obtém a soma dos itens p/ cada documento, 
            // e coloca numa nova coluna => 'documento_items_sum'.
            ->with(['toDocumento' => function ($documento) use ($date) {
                // select the columns first, so the subquery column can be added later.
                //$documento->select('id', 'date', 'total_amount', 'voucher_id');
                $documento->withCount(['hasDocumentoItems AS documento_items_sum' => function ($query) use ($date) {
                    return $query->select(DB::raw('SUM(valor)'));
                    //return $query->select(DB::raw('SUM(valor)'))->where('paid_date', '=', $date);
                }]);
            }])

            ->whereHas('toDocumento', function ($query) use ($tipo_id, $classe_id) {
                $query->where('documento_tipo_id', $tipo_id);
                //$query->where('documento_classe_id', $classe_id);

                $query->when($classe_id, function ($query, $val) {
                    $query->where('documento_classe_id', $val);
                });
            })
            ->when($mes_id, function ($query, $val) {
                $query->whereMonth('dt_baixa', $val);
            })
            ->when($ano_id, function ($query, $val) {
                $query->whereYear('dt_baixa', $val);
            })
            ->get();
        //dd($documento_baixas->toArray());

        // Obtém TOTAIS.
        $total_linhas = $documento_baixas->count();
        $valor_total = $this->formatNumber($documento_baixas->sum('valor_baixa'));

        $documento_classes = DocumentoClasse::get(['id', 'nome']);
        $documento_tipos = DocumentoTipo::get(['id', 'nome']);


        return view('test-real', compact([
            'documento_baixas',
            'documento_classes',
            'documento_tipos',
            'total_linhas',
            'valor_total',
            'tipo_id', 'classe_id', 'mes_id', 'ano_id',
        ]));
    }

    // Busca itens de Documentos.
    public function docItem(Request $request)
    {
        //dd($request->route('classe_id'));
        //dd($request->has($request->route('classe_id')));

        $tipo_id = (int)$request->route('tipo_id');
        $classe_id = (int)$request->route('classe_id');

        if ((int)$request->route('classe_id') > 0) {
            $classe_id = (int)$request->route('classe_id');
        } else {
            $classe_id = null;
        }

        $doc_items = DocumentoItem::query()
            //->with(['toDocumento'])
            ->with(['toDocumento', 'toDocumento.toPessoa', 'toPlanoCtaItem'])
            ->select('id', 'descricao', 'valor', 'documento_id')
            //->withAggregate('toPessoa', 'nome_razao')
            //->withAggregate('toDocumentoTipo', 'nome')
            //->withAggregate('toDocumento', 'data_venc')
            ->withAggregate('toDocumento', 'pessoa_id')
            ->withAggregate('toDocumento', 'documento_classe_id')
            ->withAggregate('toPlanoCtaItem', 'nome')
            ->withAggregate('toPlanoCtaItem', 'codigo')
            ->addSelect([
                'pessoa' => Pessoa::query()
                    ->select('nome_razao')->whereColumn('id', 'to_documento_pessoa_id')
                    ->latest()
                //->take(1)
            ])
            ->addSelect([
                'doc_classe' => DocumentoClasse::query()
                    ->select('nome')->whereColumn('id', 'to_documento_documento_classe_id')
                    ->latest()
                //->take(1)
            ])

            ->whereHas('toDocumento', function ($query) use ($tipo_id, $classe_id) {
                $query->where('documento_tipo_id', $tipo_id);
                //$query->where('documento_classe_id', $classe_id);

                $query->when($classe_id, function ($query, $val) {
                    $query->where('documento_classe_id', $val);
                });
            })

            //->first()
            ->get();
        dump($doc_items->toArray());

        $documento_classes = DocumentoClasse::get(['id', 'nome']);
        $documento_tipos = DocumentoTipo::get(['id', 'nome']);
        //dd($doc_item);
        //dd($doc_items->toArray());

        return view('test-doc-item', compact(['doc_items', 'documento_classes', 'documento_tipos', 'tipo_id']));
    }
    // Busca DRE Anual.
    public function dreResumoAnual(Request $request)
    {
        // #########################################
        // Prepara filtros, com base na URL
        $cta_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('cta_id')) {
            $cta_id = (int)$request->query('cta_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        /* Country::query()
        ->join('states', 'states.country_id', '=', 'countries.id')
        ->join('cities', 'cities.state_id', '=', 'states.id')
        ; */

        // #########################################
        // Busca itens no BD
        $itens_resumo = DocumentoItem::query()
            //->with('toPlanoCtaItem')
            ->select('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->selectRaw('COUNT(documento_items.id) as itens_contado, SUM(valor) as valor_somado')
            ->selectRaw('MONTH(documentos.data_venc) as month')
            ->join('plano_cta_items', 'documento_items.plano_cta_item_id', '=', 'plano_cta_items.id')
            ->join('documentos', 'documento_items.documento_id', '=', 'documentos.id')
            ->join('documento_baixas', 'documento_items.documento_id', '=', 'documentos.id')

            // Carrega DocumentoBaixa --> pronto para fazer filtros
            ->whereHas('toDocumento.hasDocumentoBaixa', function ($query) use ($mes_id, $ano_id) {
                //$query->where('cta_movimento_id', $cta_id); // Filtra pela ContaMovimento
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('dt_baixa', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
                });
            })
            ->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->groupByRaw("MONTH(documentos.data_venc)")

            ->get();

        //dump($itens_resumo->toArray());

        // #########################################
        // Trabalha com os itens obtidos no BD.
        // Remonta uma collection agrupando por níveis.

        // No total são 4 níveis (1.1.1.01 => Salário). 
        // O 4º nível, já é uma collection obtida do BD com agrupamento de cada item do Documento. 
        // Então precisamos fazer agrupamento de 3 níveis.

        // NÍVEL 3
        $itens_resumo_nivel_3 = $itens_resumo->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                    'month'         => $group->first()['month'],
                ];
            })
            ->values();

        //dd($itens_resumo_nivel_3->toArray());

        // NÍVEL 2
        $itens_resumo_nivel_2 = $itens_resumo_nivel_3->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                    'month'         => $group->first()['month'],
                ];
            })
            ->values();

        // NÍVEL 1
        $itens_resumo_nivel_1 = $itens_resumo_nivel_2->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                    'month'         => $group->first()['month'],
                ];
            })
            ->values();



        // Juntar/Unir, através do método 'merge()', a collection de cada nível: 
        // NíVEL_1 & NíVEL_2 & NíVEL_3 & NÍVEL_4
        $juntar = $itens_resumo_nivel_1
            ->merge($itens_resumo_nivel_2)->merge($itens_resumo_nivel_3)->merge($itens_resumo->toArray())
            ->sortBy('codigo')
            ->values();

        //dump($juntar->toArray());

        echo "<html><head><style>
                body {
                    font-family: 'Courier New', 
                    monospace;
                    
                }
                .nivel_1 {
                    padding-top: 25px !important;
                    background:#4682B4;
                    font-weight: bold;
                }
                .nivel_2 {
                    padding-top: 12px !important;
                    background:#6495ED;
                }
                .nivel_3 {
                    /* color: blue; */
                    background:#ADD8E6;
                }
                .nivel_4 {
                    background:#DCDCDC;
                }
                .recua {
                    padding-top: 10px;
                }
                .largura {
                    width: 1200px;
                }
                .row {
                    padding: 4px 8px;
                }
                .bg_white {
                    background: white;
                }
                table, th, td {
                    border: 1px solid;
                }
                table {
                    border-collapse: collapse;
                    font-size: 0.875em;
                }
                td.valor {
                    text-align: right;
                    height: 27px;
                    padding-left: 8px;
                    
                }
                </style>";

        // Imprimi com a formatação.

        /* $collection = collect([
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ]);
        $filtered = $collection->where('price', 100);
        $filtered->all();
        dd($collection, $filtered); */



        //dump($juntar);
        $itens_mensal = $juntar->groupBy('codigo', 'month');
        //$itens_mensal = $juntar->groupBy('codigo','month');

        //dd($itens_mensal['1.1.1']);
        echo '<table class="largura">';
        foreach ($itens_mensal as $key => $item) {

            // linha => equivale a cada código.
            //echo str_pad($key, 8, '.') . '&nbsp;&nbsp;'; // imprimi codigo na 1ª coluna
            //echo '<tr>';

            // 1ª coluna ESTÁTICA == código
            echo '<tr>';
            echo '<td class="">';
            echo $key;
            echo '</td>';
            /* echo '<td class="">';
            echo $item['nome'];
            echo '</td>'; */


            // A partir da 3ª coluna DINÂMICA ==> JAN/FEV/MAR/ABR...
            for ($i = 1; $i <= 12; $i++) {
                // abre TD
                echo '<td class="valor">';
                foreach ($item as $sub_item) {
                    $item_filtered = collect([$sub_item])->where('month', '==', $i);
                    if (count($item_filtered) > 0) {
                        //echo $i.' ';
                        echo $this->formatNumber($sub_item['valor_somado']);
                    } else {
                        //echo '0';
                    }
                }
                echo '</td>';
                // fecha TD
            }
            echo '</tr>';
        }
        echo '</table>';



        dump($itens_mensal);

        echo '<table class="largura">';
        foreach ($juntar as $item) {
            //echo $item;
            //echo '<br>';
            echo '<tr>';
            echo '<td class="">';
            echo $item['codigo'];
            echo '</td>';
            echo '<td class="">';
            echo $item['nome'];
            echo '</td>';

            echo '<td class="valor">';
            if ($item['month'] == 1) {
                //dump($i, $item['month'], $item['codigo']);
                echo $this->formatNumber($item['valor_somado']);
                echo '(' . $item['month'] . ')';
            }
            echo '</td>';
            for ($i = 1; $i <= 12; $i++) {
            }
            echo '</tr>';
        }
        echo '</table>';

        $dates = $juntar->groupBy('month');
        //dump($teste);

        // Imprimi com a formatação.
        foreach ($dates as $date => $items) {
            echo $date;
            echo '<br>';
            //echo '<table class="table table-bordered table-sm">';
            foreach ($items as $date => $items) {
                echo $items['codigo'];
                echo '<br>';
            }
            echo '<br>';
        }
    }
    public function btnMeses($ano_id)
    {
        /* {{-- MESES --}} */
        echo '<div class="flex gap-3 flex-wrap">';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=1',
        ]) . '">JAN</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=2',
        ]) . '">FEV</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=3',
        ]) . '">MAR</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=4',
        ]) . '">ABR</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=5',
        ]) . '">MAI</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=6',
        ]) . '">JUN</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=7',
        ]) . '">JUL</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=8',
        ]) . '">AGO</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=9',
        ]) . '">SET</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=10',
        ]) . '}">OUT</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=11',
        ]) . '}">NOV</a>';
        echo '<a class="btn px-2 py-1" href="' . route('dre.resumo-mensal', [
            isset($ano_id) ? 'ano_id=' . $ano_id : '',
            'mes_id=12',
        ]) . '}">DEZ</a>';
        echo '</div>';
    }
    // Busca DRE Mensal.
    public function dreResumoMensal(Request $request)
    {
        // #########################################
        // Prepara filtros, com base na URL
        $cta_id = null;
        $mes_id = null;
        $ano_id = null;

        if ($request->has('cta_id')) {
            $cta_id = (int)$request->query('cta_id');
        }
        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        // #########################################
        // Busca itens no BD
        $itens_resumo = DocumentoItem::query()
            //->with('toPlanoCtaItem')
            ->select('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->selectRaw('COUNT(documento_items.id) as itens_contado, SUM(valor) as valor_somado')
            ->join('plano_cta_items', 'documento_items.plano_cta_item_id', '=', 'plano_cta_items.id')

            // Carrega DocumentoBaixa --> pronto para fazer filtros
            ->whereHas('toDocumento.hasDocumentoBaixa', function ($query) use ($mes_id, $ano_id) {
                //$query->where('cta_movimento_id', $cta_id); // Filtra pela ContaMovimento
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('dt_baixa', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
                });
            })
            ->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->get();

        // #########################################
        // Trabalha com os itens obtidos no BD.
        // Remonta uma collection agrupando por níveis.

        // No total são 4 níveis (1.1.1.01 => Salário). 
        // O 4º nível, já é uma collection obtida do BD com agrupamento de cada item do Documento. 
        // Então precisamos fazer agrupamento de 3 níveis.


        // NÍVEL 3
        $itens_resumo_nivel_3 = $itens_resumo->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                ];
            })
            ->values();

        // NÍVEL 2
        $itens_resumo_nivel_2 = $itens_resumo_nivel_3->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                ];
            })
            ->values();

        // NÍVEL 1
        $itens_resumo_nivel_1 = $itens_resumo_nivel_2->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                    'id'            => $plano_cta->id,
                ];
            })
            ->values();

        // Juntar/Unir, através do método 'merge()', a collection de cada nível: 
        // NíVEL_1 & NíVEL_2 & NíVEL_3 & NÍVEL_4
        $juntar = $itens_resumo_nivel_1
            ->merge($itens_resumo_nivel_2)->merge($itens_resumo_nivel_3)->merge($itens_resumo->toArray())
            ->sortBy('codigo')
            ->values();

        echo "<html><head><title>DRE Mensal</title>
                <script src=\"https://cdn.tailwindcss.com\"></script>
                <style>
                    body {font-family: 'Courier New', monospace;}
                    .nivel_1 {
                        padding-top: 25px !important;
                        background:#4682B4;
                        font-weight: bold;
                    }
                    .nivel_2 {
                        padding-top: 12px !important;
                        background:#6495ED;
                    }
                    .nivel_3 {
                        /* color: blue; */
                        background:#ADD8E6;
                    }
                    .nivel_4 {
                        background:#DCDCDC;
                    }
                    .recua {
                        padding-top: 10px;
                    }
                    .largura {
                        width: 600px;
                    }
                    .row {
                        padding: 4px 8px;
                    }
                    .bg_white {
                        background: white;
                    }
                </style>
            </head>";

        // Imprimi s/ formatação.
        /* foreach ($juntar as $key => $item) {

            echo str_pad($key,'2','0', STR_PAD_LEFT) . ") ";
            echo str_pad($item['codigo'], 8, '.') . '&nbsp;&nbsp;';
            echo str_pad($this->formatNumber($item['valor_somado']), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo "<br>";

        } */
        $nivel = 0;
        $pares_impares = 1;
        $bg_class = '';
        $this->btnMeses($ano_id, $mes_id);
        // Imprimi com a formatação.
        foreach ($juntar as $key => $item) {

            $nivel_anterior = $nivel;
            $nivel = count(explode(".", $item['codigo']));

            // NIVEL 1
            if ($nivel == 1) {
                $nivel_class = 'nivel_1';
                $bg_class = '';

                // NIVEL 2
            } elseif ($nivel == 2) {
                $nivel_class = 'nivel_2';

                if ($nivel_anterior > $nivel) {
                    $nivel_class = $nivel_class . ' recua';

                    $bg_class = '';
                }
                // NIVEL 3
            } elseif ($nivel == 3) {
                $nivel_class = 'nivel_3';

                if ($nivel_anterior > $nivel) {
                    $nivel_class = $nivel_class . ' recua';
                }
                $bg_class = '';
                $pares_impares = 1;
                // NIVEL 4
            } elseif ($nivel == 4) {
                $nivel_class = 'nivel_4';

                // Se e item anterior foi nível 4
                if ($nivel_anterior == $nivel) {

                    // Se for par, BG é white.
                    if ($this->verifyNumberPar($pares_impares)) {
                        $bg_class = ' bg_white';
                        // Se não, deixa BG padrão.
                    } else {
                        $bg_class = '';
                    }
                    // Se o item anterior não foi nível 4
                } else {
                    $pares_impares = 1;
                    $bg_class = '';
                }
                $pares_impares++;
            }

            echo '<div class=" largura">';
            echo '<div class="row ' . $nivel_class . $bg_class . '">';
            echo str_pad($key, '2', '0', STR_PAD_LEFT) . ") ";
            echo str_pad($item['codigo'], 8, '.') . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item['nome']), 30, '.') . '&nbsp;&nbsp;';
            echo str_pad($this->formatNumber($item['valor_somado']), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';

            echo '</div>';
            echo '</div>';


            //echo "<br>";

        }
    }

    public function verifyNumberPar($number)
    {
        if ($number % 2 == 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getPlanoCtaPai($parent_id)
    {
        return PlanoCtaItem::query()
            ->select('id', 'codigo', 'nome', 'parent')
            ->where('id', $parent_id)
            ->get()
            ->first();
    }
    function sanitizeString($str)
    {
        // matriz de entrada
        $ent = array('%', 'º');
        $sai = array(' ', ' ');

        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        // $str = preg_replace('/[,(),;:|!"#$%&/=?~^><ªº-]/', '_', $str);
        //$str = preg_replace('/[^a-z0-9]/i', '_', $str);
        $str = preg_replace('/_+/', '_', $str); // ideia do Bacco :)
        $str = str_replace($ent, $sai, $str);
        //dd($str);
        return $str;
    }
    public function formatNumber($number)
    {
        return number_format($number, 2, ',', '.');
    }
}
