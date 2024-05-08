<?php

namespace App\Http\Controllers;

use App\Models\DocumentoItem;
use App\Models\PlanoCtaItem;
use Illuminate\Http\Request;

class DreController extends Controller
{
    public function index(Request $request)
    {
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

        /**
         * Obtém dados do BD.
         */
        $documento_items = DocumentoItem::query()
            ->select([
                'plano_cta_items.codigo', 'plano_cta_items.parent',
                \DB::raw("DATE_FORMAT(documentos.data_venc, '%Y-%m') AS month"),
                \DB::raw("COUNT(documento_items.id) AS invoices"),
                \DB::raw("SUM(valor) AS valor_sum"),
            ])
            //->selectRaw('MONTH(documentos.data_venc) as month')
            ->join('documentos', 'documento_items.documento_id', '=', 'documentos.id')
            ->join('plano_cta_items', 'documento_items.plano_cta_item_id', '=', 'plano_cta_items.id')
            ->groupBy('plano_cta_items.codigo')
            ->groupBy('plano_cta_items.parent')
            ->groupBy('month')
            //->orderBy('month')
            ->get();
        //dump($documento_items->sum('amount'));
        dump($documento_items->toArray());

        // NÍVEL 3
        $xx = $documento_items->groupBy('parent');
        //dd($xx);
        $itens_resumo_nivel_3 = $documento_items->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_sum'  => $group->sum('valor_sum'),
                    'itens_count' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'month'         => $group->first()['month'],
                    //'nome'          => $plano_cta->nome,
                    //'id'            => $plano_cta->id,
                ];
            })
            ->values();
        dump($itens_resumo_nivel_3);


        /**
         * Reorganiza os dados: agrupa/ordena.
         */
        $report = [];
        $documento_items->each(function ($item) use (&$report) {
            //$report[$item->month][$item->codigo] = [
            $report[$item->codigo][$item->month] = [
                'itens_count' => $item->invoices,
                'valor_sum' => $item->valor_sum,
            ];
        });
        dump($report);
        /* "codigo" => "1.2.3.01"
        "parent" => 26
        "month" => "2023-04"
        "invoices" => 1
        "amount" => "136.11" */


        /**
         * Lista as categorias, com base nos dados obtidos.
         * No final um "pluck('nome', 'codigo)"
         */
        $job_comp_codes = $documento_items->pluck('codigo')
            ->sortBy('codigo')
            ->unique();
        dump($job_comp_codes);

        $meses = $documento_items->pluck('month')
            ->sortBy('month')
            ->unique();
        dump($meses);

        return view('test-dreanual', compact('report', 'job_comp_codes', 'meses'));
    }
    public function dreAnual(Request $request)
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
            ->selectRaw('COUNT(documento_items.id) as itens_contado, SUM(documento_items.valor) as valor_somado')
            ->selectRaw('MONTH(documentos.data_venc) as month')
            ->join('plano_cta_items', 'documento_items.plano_cta_item_id', '=', 'plano_cta_items.id')
            ->join('documentos', 'documento_items.documento_id', '=', 'documentos.id')
            //->join('documento_baixas', 'documento_baixas.documento_id', '=', 'documentos.id')

            ->where('documento_status_id', 1)

            // Carrega DocumentoBaixa --> pronto para fazer filtros
            ->whereHas('toDocumento', function ($query) use ($mes_id, $ano_id) {
                //$query->where('cta_movimento_id', $cta_id); // Filtra pela ContaMovimento
                $query->when($ano_id, function ($query, $value) {
                    //$query->whereYear('dt_baixa', $value); // Filtra pelo mês
                    $query->whereYear('data_venc', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    //$query->whereMonth('dt_baixa', $value); // Filtra pelo ano
                    $query->whereMonth('data_venc', $value); // Filtra pelo ano
                });
            })
            //->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent', 'plano_cta_items.id')
            ->groupByRaw("MONTH(documentos.data_venc)")

            ->get();

        dump($itens_resumo->toArray());

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

        //dump(collect($juntar->toArray()));
        //dd(collect($juntar->toArray())->sum('itens_contado'));
        //dd($juntar->toArray());
        dump($juntar->toArray());

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
                .center {
                    text-align: center;                    
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



        $itens_mensal = $juntar->groupBy('codigo', 'month');
        dump($itens_mensal);
        //$itens_mensal = $juntar->groupBy('codigo', 'month');

        //dd($itens_mensal['1.1.1']);
        echo '<table class="largura">';
        echo '<tr><td class="center">CÓDIGO</TD>';
        for ($i = 1; $i <= 12; $i++) {
            echo '<td class="center">' . $i . '</td>';
        }
        echo '</tr>';
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

        /* echo '<table class="largura">';
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
        echo '</table>'; */

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

    public function getPlanoCtaPai($parent_id)
    {
        return PlanoCtaItem::query()
            ->select('id', 'codigo', 'nome', 'parent')
            ->where('id', $parent_id)
            ->get()
            ->first();
    }
    public function sanitizeString($str)
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
