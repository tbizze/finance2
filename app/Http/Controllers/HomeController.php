<?php

namespace App\Http\Controllers;

use App\Models\CtaMovimento;
use App\Models\Documento;
use App\Models\DocumentoBaixa;
use App\Models\DocumentoBaixaTipo;
use App\Models\DocumentoClasse;
use App\Models\DocumentoItem;
use App\Models\DocumentoTipo;
use App\Models\Pessoa;
use App\Models\PlanoCtaItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

use function Termwind\style;

class HomeController extends Controller
{


    public function home()
    {
        /* https://www.w3schools.com/w3css/w3css_downloads.asp */
        echo '<!DOCTYPE html>
                <html>
                <title>My Web</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
                ';
        echo '<div class="w3-container w3-display-topmiddle w3-padding-16 w3-card w3-margin">';
        echo '<div class="w3-cell">';
        echo '<a class="w3-btn w3-green w3-round" href="' . route('real', 3) . '" >Cta. Paga</a>';
        echo '<a class="w3-btn w3-green w3-round w3-margin-left" href="' . route('previsao', 1) . '" >Cta. a Pagar</a>';
        echo '<a class="w3-btn w3-green w3-round w3-margin-left" href="' . route('doc.item', 1) . '" >Itens de Documentos</a>';
        echo '<a class="w3-btn w3-green w3-round w3-margin-left" href="' . route('dre.resumo-mensal', 'ano_id=2023&mes_id=1') . '" >DRE mensal</a>';
        echo '<a class="w3-btn w3-green w3-round w3-margin-left" href="' . route('dre.resumo-anual', 'ano_id=2023') . '" >DRE anual</a>';
        echo '<a class="w3-btn w3-green w3-round w3-margin-left" href="' . route('conf.all', 'ano_id=2023') . '" >Conferência</a>';
        echo '</div>';
        echo '</div>';
    }
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

        echo "<html><head><style>
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
                </style>";

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
    public function dreResumo3(Request $request)
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

        // Busca IDs de itens
        $itens_resumo = DocumentoItem::query()
            //->with('toPlanoCtaItem')
            ->select('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent')
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
            ->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent')
            ->get();

        //$itens_resumo->groupBy('parent');
        /* $itens_resumo_group1 = $itens_resumo ->groupBy('parent')
            ->map(function ($item) {
                return $item->sum('valor_somado');
        }); */

        // usaremos map para acumular cada grupo de linhas em uma única linha.
        // $group é uma coleção de linhas que possuem o mesmo 'position_id'.
        /* $groupwithcount = $groups->map(function ($group) {
            return [
                'opposition_id' => $group->first()['opposition_id'], // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                'points' => $group->sum('points'),
                'won' => $group->where('result', 'won')->count(),
                'lost' => $group->where('result', 'lost')->count(),
            ];
        }); */

        // No total são 4 níveis (1.1.1.01 => Salário). O quarto é item de lançamento. 
        // Então precisamos fazer agrupamento de 3 níveis.

        // NÍVEL 3
        $itens_resumo_nivel_3 = $itens_resumo->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    //$group->sum('valor_somado'),
                    'parent'        => $plano_cta->parent, // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    //'parent'        => $group->first()['parent'], // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                ];
            })
            ->values();

        // NÍVEL 2
        $itens_resumo_nivel_2 = $itens_resumo_nivel_3->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    //$group->sum('valor_somado'),
                    'parent'        => $plano_cta->parent, // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    //'parent'        => $group->first()['parent'], // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                ];
            })
            ->values();

        // NÍVEL 1
        $itens_resumo_nivel_1 = $itens_resumo_nivel_2->groupBy('parent')
            ->map(function ($group) {
                $plano_cta = $this->getPlanoCtaPai($group->first()['parent']);
                return [
                    //$group->sum('valor_somado'),
                    'parent'        => $plano_cta->parent, // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    //'parent'        => $group->first()['parent'], // O 'position_id' é constante dentro do mesmo grupo, então pegue o primeiro.
                    'valor_somado'  => $group->sum('valor_somado'),
                    'itens_contado' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    'nome'          => $plano_cta->nome,
                ];
            })
            ->values();

        $juntar = $itens_resumo_nivel_1
            ->merge($itens_resumo_nivel_2)->merge($itens_resumo_nivel_3)->merge($itens_resumo->toArray())
            ->sortBy('codigo')
            ->values();
        //$juntar = $juntar->merge($itens_resumo_nivel_3);
        dump($juntar, $itens_resumo->toArray());

        echo "<html><head><style>
                body {font-family: 'Courier New', monospace;}
                </style>";

        foreach ($juntar as $key => $item) {
            echo "<br>";
            //array_push($total_anterior, $item->valor_somado);
            echo str_pad($item->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->formatNumber($item->valor_somado), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo "<br>";
            //echo str_pad($parent_cta->codigo, 8, '.') . '&nbsp;&nbsp;';
            //echo str_pad($this->formatNumber($item->valor_somado), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            //echo '<br>';

        }



        dd($itens_resumo_nivel_1, $itens_resumo_nivel_2, $itens_resumo->toArray());

        //$chats = Message::where('toId',$id)->orWhere('fromId', $id)->latest('id')->with('sender','recipient')->get();
        $parent = '';
        $total_anterior = [];
        $inbox = [];
        $itens_array = [];
        $count_subitems = 0;
        $item_anterior = [];

        echo "<html><head><style>
                body {font-family: 'Courier New', monospace;}
                </style>";

        foreach ($itens_resumo as $key => $item) {
            /* if (!in_array($value['fromId'], $fromIds)) {
                array_push($inbox,$value);
                array_push($fromIds,$value['fromId']);
            } */
            //$codigo_anterior = $item->codigo;

            //$key++;

            if ($item_anterior == []) {
                dump($key . ') ' . 'anterior NULL // ' . $item->codigo);

                // imprimi item atual
                echo $item->codigo;
                echo '<br>';
            } else {
                dump($key . ') ' . $item_anterior->codigo . ' // ' . $item->codigo);

                // anterior <> atual
                if ($item_anterior->parent != $item->parent) {
                    dump('<> ' . $item_anterior->parent . ' // ' . $item->parent);

                    // imprime resumo do anterior
                    // aqui pode usar uma função, que busca dados a partir do parent do anterior
                    $fecha_anterior = $this->getPlanoCtaPai($item_anterior->parent);
                    echo $fecha_anterior->codigo;
                    echo '<br>';

                    // imprimi item atual
                    echo $item->codigo;
                    echo '<br>';

                    // anterior == atual
                } else {
                    dump('== ' . $item_anterior->parent . '// ' . $item->parent);

                    // imprimi item atual
                    echo $item->codigo;
                    echo '<br>';
                }
            }



            //echo "<br>";
            //echo str_pad($key, 2, '0', STR_PAD_LEFT) . '&nbsp;&nbsp;';

            if ($parent != $item->parent) {
                //if ($parent != $item->parent){
                $parent_cta = $this->getPlanoCtaPai($item->parent);
                //$total_anterior = [];

                //dump(($total_anterior));
                //if ($count_subitems == 0 ){
                /* if ($total_anterior == [] ){
                    //dump('array null');
                    echo "<br>";
                    //array_push($total_anterior, $item->valor_somado);
                    echo str_pad($item->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                    echo str_pad($this->formatNumber($item->valor_somado), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                    echo "<br>";
                    echo str_pad($parent_cta->codigo, 8, '.') . '&nbsp;&nbsp;';
                    echo str_pad($this->formatNumber($item->valor_somado), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                    echo '<br>';
                }else{
                    echo '<br>xxxxx';
                    $val_sum = collect($total_anterior);

                    if ($total_anterior != [] ){
                        echo "<br>---------------";
                        echo str_pad($parent_cta->codigo, 8, '.') . '&nbsp;&nbsp;';
                        echo str_pad($this->formatNumber($val_sum->sum()), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                        //echo str_pad($item->valor_somado, 12, '.', STR_PAD_LEFT);
                        //echo $parent_cta->codigo . "<br>";
                        //echo $item->codigo;
                        echo "<br>";
                    }
                } */




                //echo "<br>";


                //$total_anterior = [];
            } else {

                /* //echo str_pad($key, 2, '0', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                //echo str_pad($parent_cta->codigo, 8, '.') . '&nbsp;&nbsp;';
                //echo "<br>";
                echo str_pad($item->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                //echo str_pad($item->valor_somado, 12, '.', STR_PAD_LEFT);
                echo str_pad($this->formatNumber($item->valor_somado), 12, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
                //echo $parent_cta->codigo . "<br>";
                //echo $item->codigo;
                echo "<br>";

                array_push($total_anterior, $item->valor_somado); */
            }
            // Coloca na memória o parent rodado.
            $parent = $item->parent;
            $item_anterior = $item;

            /* $key = $key++;
            dump($key . ') codigo loop: '.$item->codigo);

            if ($parent == $item->parent){
                dump('  mesmo PARENT:'.$parent);

                array_push($itens_array, $item);
                array_push($total_anterior, $item->valor_somado);
                $parent = $item->codigo;
                dump($total_anterior);
            }else{
                dump('  PARENT diferente: '.$parent);
                if ($parent == '') {
                    dump('PARENT null');
                    $parent = $item->parent;
                    array_push($total_anterior, $item->valor_somado);
                }
                
                //dump($total_anterior);
                $val_sum = collect($total_anterior);
                //dump($val_sum->sum());
            
                $plano_cta_items = PlanoCtaItem::query()
                    ->select('codigo','nome','parent')
                    ->where('id', $item->parent)
                    ->get()
                    ->first()
                    ;
                //dd($plano_cta_items->codigo);
                $new_item = [
                    "codigo" => $plano_cta_items->codigo,
                    "nome" => $plano_cta_items->nome,
                    "parent" => $plano_cta_items->parent,
                    "itens_contado" => $val_sum->count(),
                    "valor_somado" => $val_sum->sum(),
                    "to_plano_cta_item" => null,
                ];
                //dd($new_item);
                array_push($itens_array, $new_item);
                $total_anterior = [];

                //dump($codigo_anterior);
                //dump($total_anterior);
                
            }
            $parent = $item->parent; */
        }

        //dd($itens_array);

        /* "codigo" => "1.1.1.01"
        "nome" => "Salário"
        "parent" => 8
        "itens_contado" => 1
        "valor_somado" => "40965.84"
        "to_plano_cta_item" => null */
    }
    public function getPlanoCtaPai($parent_id)
    {
        return PlanoCtaItem::query()
            ->select('id', 'codigo', 'nome', 'parent')
            ->where('id', $parent_id)
            ->get()
            ->first();
    }
    public function dreResumo2(Request $request)
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

        // Busca IDs de itens
        $itens_resumo = DocumentoItem::query()
            ->with('toPlanoCtaItem')
            ->select('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent')
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
            ->groupBy('plano_cta_items.codigo', 'plano_cta_items.nome', 'plano_cta_items.parent')
            ->get();
        dump($itens_resumo->toArray());

        $total_linhas = $itens_resumo->count();
        $valor_total = $this->formatNumber($itens_resumo->sum('valor_somado'));

        foreach ($itens_resumo as $key => $item) {
            $key++;

            echo "<html><head><style>
            body {font-family: 'Courier New', monospace;}
            </style>";

            echo str_pad($key, 3, '0', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($item->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item->nome), 30, '.') . '&nbsp;&nbsp;';
            echo str_pad($this->formatNumber($item->valor_somado), 15, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo 'Qde. Itens (' . str_pad($item->itens_contado, 3, '.', STR_PAD_LEFT) . ')' . '&nbsp;&nbsp;';
            echo '<br>';
        }
        echo '<br>';
        echo
        '<div class="flex gap-3 px-3 py-3 flex-wrap">
                <div class=" bg-gray-100 py-1 px-2 rounded-md">Total de itens: ' . $total_linhas . '</div>
                <div class="bg-gray-100 py-1 px-2 rounded-md">Soma total: ' . $valor_total . '</div>
            </div>';
    }
    public function dreResumo(Request $request)
    {
        $cta_id = (int)$request->route('cta_id');

        $mes_id = null;
        $ano_id = null;

        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        // monta consulta.
        $itens_dre_resumo = DocumentoItem::query()
            ->select('plano_cta_item_id')
            ->selectRaw('COUNT(id) as itens_contado, SUM(valor) as valor_somado')
            // Carrega DocumentoBaixa --> pronto para fazer filtros
            ->whereHas('toDocumento.hasDocumentoBaixa', function ($query) use ($cta_id, $mes_id, $ano_id) {
                //$query->where('cta_movimento_id', $cta_id); // Filtra pela ContaMovimento
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('dt_baixa', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
                });
            })
            ->groupBy('plano_cta_item_id')
            ->get()
            //->toSql()
        ;

        //dd($itens_dre_resumo);

        $total_linhas = $itens_dre_resumo->count();
        $valor_total = $this->formatNumber($itens_dre_resumo->sum('valor_somado'));

        //dump('Total itens:' . $total_linhas . ' // Soma total:' . $valor_total);
        dump($itens_dre_resumo->toArray());

        foreach ($itens_dre_resumo as $key => $item) {
            echo "<html><head><style>
            body {font-family: 'Courier New', monospace;}
            </style>";

            echo str_pad($key, 3, '0', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($item->plano_cta_item_id, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->formatNumber($item->valor_somado), 15, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo 'Qde. Itens (' . str_pad($item->itens_contado, 3, '.', STR_PAD_LEFT) . ')' . '&nbsp;&nbsp;';
            echo '<br>';
        }
        echo '<br>';
        echo
        '<div class="flex gap-3 px-3 py-3 flex-wrap">
                <div class=" bg-gray-100 py-1 px-2 rounded-md">Total de itens: ' . $total_linhas . '</div>
                <div class="bg-gray-100 py-1 px-2 rounded-md">Soma total: ' . $valor_total . '</div>
            </div>';
    }
    public function formatNumber($number)
    {
        return number_format($number, 2, ',', '.');
    }
    public function dreItems(Request $request)
    {
        $cta_id = (int)$request->route('cta_id');

        $mes_id = null;
        $ano_id = null;

        if ($request->has('mes_id')) {
            $mes_id = (int)$request->query('mes_id');
        }
        if ($request->has('ano_id')) {
            $ano_id = (int)$request->query('ano_id');
        }

        $itens_dre = DocumentoItem::query()
            ->with(['toPlanoCtaItem'])
            ->with(['toDocumento.hasDocumentoBaixa'])
            ->select('id', 'valor', 'descricao', 'plano_cta_item_id', 'documento_id')
            ->withAggregate('toPlanoCtaItem', 'codigo')
            //->withAggregate('toPlanoCtaItem', 'natureza')

            //->selectRaw("(CASE WHEN (valor > 1000) THEN '>>' ELSE '<<' END) as valor_cal")

            /* ->with(['toPlanoCtaItem' => function($q) {
            $q->orderBy('codigo', 'asc');
        }]) */

            // Carrega DocumentoBaixa --> pronto para fazer filtros
            ->whereHas('toDocumento.hasDocumentoBaixa', function ($query) use ($cta_id, $mes_id, $ano_id) {
                $query->where('cta_movimento_id', $cta_id); // Filtra pela ContaMovimento
                $query->whereMonth('dt_baixa', $mes_id); // Filtra pelo mês
                $query->whereYear('dt_baixa', $ano_id); // Filtra pelo ano
            })

            // Ordena pelo código da categoria contábil.
            ->orderBy('to_plano_cta_item_codigo')
            ->limit(100)
            ->get()
            //->first()
        ;
        dump($itens_dre->toArray());

        foreach ($itens_dre as $item) {
            echo "<html><head><style>
            body {font-family: 'Courier New', monospace;}
            </style>";
            echo str_pad($item->id, 3, '0', STR_PAD_LEFT) . '] ';
            echo str_pad($item->toPlanoCtaItem->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item->toPlanoCtaItem->nome), 30, '.') . '&nbsp;&nbsp;';
            echo str_pad($item->valor, 10, '.', STR_PAD_LEFT) . '&nbsp;';
            echo str_pad($item->toPlanoCtaItem->natureza, 1, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item->descricao), 30, '.') . '&nbsp;&nbsp;';
            echo $item->toDocumento->hasDocumentoBaixa->dt_baixa->format('d/m/Y') . '&nbsp;&nbsp;';
            /* echo str_pad($this->sanitizeString($item->toDocumento->toPessoa->nome_razao),35,'.') . ' ';
            echo str_pad($item->toDocumento->toDocumentoClasse->nome,15,'.') . '&nbsp;&nbsp;';
            echo str_pad($item->toDocumento->codigo,4,'0',STR_PAD_LEFT) . '&nbsp;&nbsp;'; */
            echo '<br>';
        }
    }
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
    public function docItem(Request $request)
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

        /* $tipo_id = (int)$request->route('tipo_id');
        $classe_id = (int)$request->route('classe_id');

        if ((int)$request->route('classe_id') > 0) {
            $classe_id = (int)$request->route('classe_id');
        } else {
            $classe_id = null;
        } */

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

            ->whereHas('toDocumento', function ($query) use ($tipo_id, $classe_id, $ano_id, $mes_id) {
                $query->where('documento_tipo_id', $tipo_id);
                //$query->where('documento_classe_id', $classe_id);

                $query->when($ano_id, function ($query, $val) {
                    $query->whereYear('data_venc', $val);
                });
                $query->when($mes_id, function ($query, $val) {
                    $query->whereMonth('data_venc', $val);
                });
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

        return view('test-doc-item', compact(['doc_items', 'documento_classes', 'documento_tipos', 'tipo_id', 'mes_id', 'ano_id']));
    }
    public function documento($tipo_id, $classe_id = null)
    {
        $documentos = Documento::query()
            ->with(['hasDocumentoBaixa', 'hasDocumentoItems'])
            ->withAggregate('toPessoa', 'nome_razao')
            ->withAggregate('toDocumentoStatus', 'nome')
            ->withAggregate('toDocumentoClasse', 'nome')
            ->withAggregate('toDocumentoTipo', 'nome')
            ->withSum(['hasDocumentoItems'], 'valor')
            ->withCount(['hasDocumentoItems'], 'id')
            ->withCount(['hasDocumentoBaixa'], 'id')

            ->when($tipo_id, function ($query, $val) {
                switch ($val) {
                    case 1:
                        $query->tpAPagar();
                        break;
                    case 2:
                        $query->tpAReceber();
                        break;
                    case 3:
                        $query->tpPago();
                        break;
                    case 4:
                        $query->tpRecebido();
                        break;
                    case 5:
                        $query->tpTarifa();
                        break;
                    case 6:
                        $query->tpMovimento();
                        break;
                }
                //$query->where('name', 'like', '%' . $val . '%');

                return $query;
            })
            ->get();
        $documento_classes = DocumentoClasse::get(['id', 'nome']);
        $documento_tipos = DocumentoTipo::get(['id', 'nome']);

        //dd($documentos);

        return view('test-doc', compact(['documentos', 'documento_classes', 'documento_tipos']));
    }

    public function test()
    {
        // Retorna autores e uma lista concatenada de títulos de posts
        //$authorsWithPosts = Author::with('posts')->selectRaw('authors.*, GROUP_CONCAT(posts.title) as post_titles')->groupBy('authors.id')->get();  
        /* $docWithItens = Documento::query()
        ->with('hasDocumentoItems')
        ->selectRaw('documentos.*, GROUP_CONCAT(codigo) as docitem_descricao')
        //->selectRaw('documentos.*, GROUP_CONCAT(hasDocumentoItems.descricao) as docitem_descricao')
        ->groupBy('documentos.id')
        ->get(); */


        // Retorna a contagem de posts agrupados por status, apenas aqueles com mais de 5 posts
        //$postsCountByStatus = Post::groupBy('status')->havingRaw('count(*) > 5')->get();
        /* $postsCountByStatus2 = Documento::query()
        ->groupBy('documento_classe_id')
        ->havingRaw('count(*) > 1')
        ->get()
        ; */

        // Retorna a contagem de posts agrupados por status
        //$postsCountByStatus = Post::groupBy('status')->selectRaw('status, count(*) as count')->get();
        /* $postsCountByStatus = Documento::query()
        ->selectRaw('documento_classe_id, count(*) as count')
        ->groupBy('documento_classe_id')
        ->get(); */

        // Retorna a contagem de posts agrupados por autor
        //$postsCountByAuthor = Post::groupBy('author')->selectRaw('author, count(*) as post_count')->get();
        /* $postsCountByAuthor = DocumentoItem::query()
        ->groupBy('documento_id')
        ->selectRaw('documento_id, count(*) as documento_item_count')
        ->get(); */

        // Retorna a soma dos 'likes' aprovados para cada post
        //$postsWithApprovedLikesSum = Post::withSum('comments', 'likes')->get();
        $postsWithApprovedLikesSum = Documento::query()
            ->withSum('hasDocumentoItems', 'valor')
            ->withCount(['hasDocumentoItems'], 'id')
            ->withCount(['hasDocumentoBaixa'], 'id')
            ->get();

        dump($postsWithApprovedLikesSum->toArray());
        //dump($postsCountByStatus, $postsCountByAuthor->toArray(),);
        //dd($postsCountByStatus->toArray());

    }
    public function test1()
    {
        /* $dados = DocumentoItem::query()
        ->with(['toDocumento'])
        ->select('id','descricao','documento_id')
        //->selectRaw('documento_items.id, documento_items.valor as valor_dobrado')
        ->selectRaw('documento_items.valor, (documento_items.valor * 2) as valor_dobrado')
        ->get()
        ->groupBy('documento_id')
        //->dd()
        ; */

        //dump($dados);
        //dump($dados->toArray());

        /* $sumItens = Documento::query()
        ->withSum('hasDocumentoItems', 'valor')
        ->withCount(['hasDocumentoItems'], 'id')
        ->groupBy('documento_items.id')
        ->get();

        dump($sumItens->toArray()); */

        $date = 3;
        $baixas = Documento::query()
            //->whereMonth('data_venc', $date)
            ->whereIn('id', [78, 64, 49])
            ->where('documento_status_id', 1)
            ->with(['hasDocumentoItems' => function ($expense) use ($date) {
                // select the columns first, so the subquery column can be added later.
                $expense->select('id', 'descricao', 'valor', 'documento_id');
            }])
            ->withCount(['hasDocumentoBaixa AS pgto_sum' => function ($query) use ($date) {
                $query->select(DB::raw('SUM(valor_baixa)'))->whereMonth('dt_compensa', 3);
            }])
            ->get();

        dump($baixas->toArray());


        // Voucher = comprovante // expenses = despesas // paid_sum = soma paga // paid_amount = qde paga
        // Voucher     => Expense => Payment
        // Comprovante => Despesa => Pagamento
        /* Voucher::query()
        ->where('voucher_date', $request->date)
        ->where('account_id', 1)
        ->with(['expenses' => function ($expense) use ($date) {
            // select the columns first, so the subquery column can be added later.
            $expense->select('id', 'date', 'total_amount', 'voucher_id');
            $expense->withCount(['expPaymentLiab AS paid_sum' => function ($query) use ($date) {
                return $query->select(DB::raw('SUM(paid_amount)'))->where('paid_date', '=', $date);
             }]);
        }])
        ->get(); */
    }
    public function test2()
    {
        $test = DocumentoItem::query()
            ->with(['toDocumento', 'toDocumento.toPessoa', 'toDocumento.toDocumentoClasse', 'toPlanoCtaItem'])
            //->withAggregate('toDocumento', 'documento_tipo_id')
            //->withAggregate('toDocumento', 'documento_classe_id')
            //->withAggregate('toDocumento', 'pessoa_id')
            /* 
        ->withAggregate('toDocumentoClasse', 'nome')
        ->withAggregate('toDocumentoTipo', 'nome') */
            /* ->withSum(['hasDocumentoItems'], 'valor')
        ->withCount(['hasDocumentoItems'], 'id') */
            //->withCount(['hasDocumentoBaixa'], 'id')
            /* ->addSelect([
            'doc_classe' => DocumentoClasse::query()
                ->select('nome')->whereColumn('id', 'to_documento_documento_classe_id')
                ->latest()
                //->take(1)
        ]) */
            /* ->addSelect([
            'pessoa' => Pessoa::query()
                ->select('nome_razao')->whereColumn('id', 'to_documento_pessoa_id')
                ->latest()
                //->take(1)
        ]) */

            //->get()
            ->dd()
            //->sum('valor')
        ;

        /* $count = DocumentoItem::query()
        ->with(['toDocumento', 'toDocumento.toPessoa', 'toDocumento.toDocumentoClasse','toPlanoCtaItem'])
        ->get()
        //->sum('valor')
        ->count(); */

        //dump($test);
        dd($test);
        //dump('$test->toArray()');


        //->sum('valor')

        foreach ($test as $item) {
            echo "<html><head><style>
            body {font-family: 'Courier New', monospace;}
            </style>";
            echo str_pad($item->id, 3, '0', STR_PAD_LEFT) . '] ';
            echo str_pad($this->sanitizeString($item->toDocumento->toPessoa->nome_razao), 35, '.') . ' ';
            echo $item->toDocumento->data_venc->format('d/m/Y') . '&nbsp;&nbsp;';
            echo str_pad($item->toDocumento->toDocumentoClasse->nome, 15, '.') . '&nbsp;&nbsp;';
            echo str_pad($item->toDocumento->codigo, 4, '0', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($item->valor, 10, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($item->toPlanoCtaItem->codigo, 8, '.', STR_PAD_LEFT) . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item->toPlanoCtaItem->nome), 30, '.') . '&nbsp;&nbsp;';
            echo str_pad($this->sanitizeString($item->descricao), 30, '.') . '&nbsp;&nbsp;';
            echo '<br>';
        }

        //dd($i, $j, $test->toArray());
        /* $now = $this->faker->date();

        if ($data_emissao >= $now) {
            $data_emissao = $now;
        } */
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

    public function testDate()
    {
        // obtendo o tempo atual
        $current = Carbon::now();
        // adicionando 30 dias a partir do tempo/dia atual
        //$trialExpires =  $current->addDays(30);
        $trialExpires =  $current->addYears(3);

        dump($current->addDays(3));
        dump($current->addMonths(3));
        dump($current->addYears(3));


        dd($current->toDateTimeString(), $trialExpires->toDateTimeString());
    }
}
