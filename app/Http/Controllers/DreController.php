<?php

namespace App\Http\Controllers;

use App\Models\DocumentoItem;
use App\Models\PlanoCtaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class DreController extends Controller
{
    public function anualSum(Request $request)
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
            ->groupBy('plano_cta_items.codigo')
            ->groupBy('plano_cta_items.parent')
            ->groupBy('month')
            //->orderBy('month')
            ->get();
        //dump($documento_items->toArray());

        /**
         * Lista as categorias, com base nos dados obtidos.
         * Ordena por 'codigo' // Limpa coleção com pluck('codigo') // Aplica 'unique()' para não repetir meses.
         */
        $codigos = $documento_items
            ->sortBy('codigo')
            ->pluck('codigo')
            ->unique();
        //dump($codigos);

        /**
         * Lista os meses, com base nos dados obtidos.
         * Ordena por month // Limpa coleção com pluck('month') // Aplica 'unique()' para não repetir meses.
         */
        $meses = $documento_items
            ->sortBy('month')
            ->pluck('month')
            ->unique();
        //dump($meses);


        /**
         * ###### NÍVEL 04
         * Monta resumo/agrupamento no nível 4. Então primeiro agrupa os itens na mesma categoria ('codigo').
         * Depois agrupa também por mês, para que na view seja possível separar em cada coluna o respectivo mês.
         * NOTA: se não agrupar também por mês, terá uma soma anual.
         * 
         * BASE DE DADOS: usamos dados oriundos do BD, que estão em '$documento_items. 
         * Esses chegam com agrupamento do maior nível, isto é, não chega aqui cada item, mas 
         * a soma dos itens de cada categoria de lançamento.
         */
        $dre_all = [];

        $dre_montado4 = [];
        $items_nivel4 = $documento_items
            ->groupBy(['codigo', 'month'])
            ->each(function ($item) use (&$dre_montado4, &$dre_all) {
                foreach ($item as $item) {
                    // Acrescenta na '$dre_montado4', os itens montados deste nível.
                    // NOTE: Este maior nível (04) não é tomado como base para o nível inferior. Talvez não seja necessário a '$dre_montado4'.
                    $dre_montado4[$item->first()->codigo][$item->first()['month']] = [
                        'parent'        => $item->first()->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $item->first()->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->first()['valor_sum'],
                        'itens_count'   => $item->first()['invoices'],
                    ];
                    // Acrescenta na '$dre_all', que une todos os níveis, os itens montados deste nível.
                    $dre_all[$item->first()->codigo][$item->first()['month']] = [
                        'parent'        => $item->first()->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $item->first()->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->first()['valor_sum'],
                        'itens_count'   => $item->first()['invoices'],
                    ];
                }
            });
        //dump($dre_montado4);

        /**
         * ###### NÍVEL 03
         * Monta resumo/agrupamento no nível 3. Então primeiro agrupa as categorias que tem mesma categoria pai ('parent').
         * Depois agrupa também por mês, para que na view seja possível separar em cada coluna o respectivo mês.
         * NOTA: se não agrupar também por mês, terá uma soma anual.
         * 
         * BASE DE DADOS: usamos dados oriundos do BD, que estão em '$documento_items. 
         * Esses chegam com agrupamento do maior nível, isto é, não chega aqui cada item, mas 
         * a soma dos itens de cada categoria de lançamento.
         */
        $dre_montado3 = [];
        $items_nivel3 = $documento_items
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado3, &$dre_all) {
                foreach ($item as $item) {
                    // Obtém dados da categoria pai como código...
                    $categoria_pai = $this->getPlanoCtaPai($item->first()['parent']);
                    // Acrescenta na '$dre_montado3', os itens montados deste nível.
                    // Esta '$dre_montado3' será usada para montar o nível 02.
                    $dre_montado3[$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                    // Acrescenta na '$dre_all', que une todos os níveis, os itens montados deste nível.
                    $dre_all[$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                }
            });
        //dump($dre_montado3);

        /**
         * ###### NÍVEL 02
         * Monta resumo/agrupamento no nível 2. Então primeiro agrupa as categorias que tem mesma categoria pai ('parent').
         * Depois agrupa também por mês, para que na view seja possível separar em cada coluna o respectivo mês.
         * NOTA: se não agrupar também por mês, terá uma soma anual.
         * 
         * BASE DE DADOS: usamos o nível montado anterior '$dre_montado3'. Nesta coleção é aplicado o método 'flatten(1)',
         * isto irá reduzir 01 dimensão do array, ou seja, removerá o agrupamento por mês.
         * Então posso aplicar a mesma regra de negócio do nível anterior.
         */
        $dre_montado2 = [];
        $items_nivel2 = collect($dre_montado3)->flatten(1)
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado2, &$dre_all) {
                foreach ($item as $item) {
                    // Obtém dados da categoria pai como código...
                    $categoria_pai = $this->getPlanoCtaPai($item->first()['parent']);
                    // Acrescenta na '$dre_montado2', os itens montados deste nível.
                    // Esta '$dre_montado2' será usada para montar o nível 01.
                    $dre_montado2[$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                    // Acrescenta na '$dre_all', que une todos os níveis, os itens montados deste nível.
                    $dre_all[$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                }
            });
        //dump($dre_montado2);

        /**
         * ###### NÍVEL 01
         * Monta resumo/agrupamento no nível 1. Então primeiro agrupa as categorias que tem mesma categoria pai ('parent').
         * Depois agrupa também por mês, para que na view seja possível separar em cada coluna o respectivo mês.
         * NOTA: se não agrupar também por mês, terá uma soma anual.
         * 
         * BASE DE DADOS: usamos o nível montado anterior '$dre_montado2'. Nesta coleção é aplicado o método 'flatten(1)',
         * isto irá reduzir 01 dimensão do array, ou seja, removerá o agrupamento por mês.
         * Então posso aplicar a mesma regra de negócio do nível anterior.
         */
        $dre_montado1 = [];
        $items_nivel1 = collect($dre_montado2)->flatten(1)
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado1, &$dre_all) {
                foreach ($item as $item) {
                    // Obtém dados da categoria pai como código...
                    $categoria_pai = $this->getPlanoCtaPai($item->first()['parent']);
                    // Acrescenta na '$dre_montado1', os itens montados deste nível.
                    // NOTE: Por ser o nível bais baixo, talvez não seja necessário a '$dre_montado1'.
                    $dre_montado1[(string)$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                    // Acrescenta na '$dre_all', que une todos os níveis, os itens montados deste nível.
                    $dre_all[(string)$categoria_pai->codigo][$item->first()['month']] = [
                        'parent'        => $categoria_pai->parent, // Pega 'parent' de '$categoria_pai, e não da linha atual.
                        'codigo'        => $categoria_pai->codigo, // Pega 'codigo' de '$categoria_pai, e não da linha atual.
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'), // Soma todos os itens deste agrupamento (parent / month).
                        'itens_count'   => $item->count(), // Conta quantos itens deste agrupamento (parent / month).
                    ];
                }
            });

        // Com o método 'sortKeys()', ordena a coleção pela chave/índice.
        $report_all = collect($dre_all)->sortKeys();


        /**
         * ###### TOTAL DE CADA CATEGORIA
         * Através do método 'map()', itera a coleção, retornando uma nova coleção, modificando conforme necessário.
         * 
         * BASE DE DADOS: usamos os dados organizados acima em cada nível: '$report_all'. 
         * Nesta coleção é aplicado o método 'flatten(1)', para reduzir 01 dimensão do array, ou seja, removerá o agrupamento por mês.
         * Então agrupo por código, e com o método 'map() faço loop em cada item, retornando a suma da categoria e quantos itens.
         */
        $report_all_total = collect($report_all)
            ->flatten(1)
            ->groupBy(['codigo'])
            ->map(function ($group) {
                return [
                    'valor_sum'  => $this->currencyGetDb($group->sum('valor_sum'), 2, '.'),
                    'itens_count' => $group->count(),
                ];
            });
        dump($report_all_total->toArray());


        return view('test-dreanual', compact('codigos', 'meses', 'report_all', 'report_all_total'));
    }
    public function anual(Request $request)
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
            ->groupBy('plano_cta_items.codigo')
            ->groupBy('plano_cta_items.parent')
            ->groupBy('month')
            //->orderBy('month')
            ->get();
        //dump($documento_items->toArray());

        /**
         * Lista as categorias, com base nos dados obtidos.
         * Ordena por 'codigo' // Limpa coleção com pluck('codigo') // Aplica 'unique()' para não repetir meses.
         */
        $codigos = $documento_items
            ->sortBy('codigo')
            ->pluck('codigo')
            ->unique();
        //dump($codigos);

        /**
         * Lista os meses, com base nos dados obtidos.
         * Ordena por month // Limpa coleção com pluck('month') // Aplica 'unique()' para não repetir meses.
         */
        $meses = $documento_items
            ->sortBy('month')
            ->pluck('month')
            ->unique();
        //dump($meses);

        // NÍVEL 3
        //dd($documento_items->toArray());
        //$xx = $documento_items->groupBy(['codigo']);

        // BUG: Um erro/falha - soma também meses e não somente parent.
        // Se existir na mesma categoria itens de outros meses, está pegando somente o 1º mês: $group->first()['month'],
        // Tentar agrupar não somente por 'parent' mas também por 'month.
        /* $xz = 0;
        $itens_resumo_nivel_3 = $documento_items->groupBy(['parent', 'month'])
            ->map(function ($group) use (&$xz) {
                $xz++;
                //dump($group->toArray());
                foreach ($group as $item) {
                    //dump($item->toArray());
                    $plano_cta = $this->getPlanoCtaPai($item->first()['parent']);
                    //dump($plano_cta->toArray());
                    //dd($group['2023-03']->first()['parent']);
                    return [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'valor_sum'  => $item->sum('valor_sum'),
                        'itens_count' => $item->count(),
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                    ];
                }
                //dd('fim');
                return [
                    'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                    'valor_sum'  => $group->sum('valor_sum'),
                    'itens_count' => $group->count(),
                    'codigo'        => $plano_cta->codigo,
                    // Veja que da forma como está usa o ->first(), ou seja, pega somente o 1º mês ocorrido.
                    // Justamente por isso nos outras meses a hierarquia daquela conta não aparece.
                    'month'         => $group->first()['month'],
                    //'nome'          => $plano_cta->nome,
                    //'id'            => $plano_cta->id,
                ];
            })
            ->values();
        dump('nivel 3' . $xz);
        dump($itens_resumo_nivel_3); */

        $dre_all = [];
        $dre_montado4 = [];
        $items_nivel4 = $documento_items
            ->groupBy(['codigo', 'month'])
            ->each(function ($item) use (&$dre_montado4, &$dre_all) {
                foreach ($item as $item) {
                    $dre_montado4[$item->first()->codigo][$item->first()['month']] = [
                        'parent'        => $item->first()->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $item->first()->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->first()['valor_sum'],
                        'itens_count'   => $item->first()['invoices'],
                    ];
                    $dre_all[$item->first()->codigo][$item->first()['month']] = [
                        'parent'        => $item->first()->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $item->first()->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $this->formatNumber($item->first()['valor_sum']),
                        'itens_count'   => $item->first()['invoices'],
                    ];
                }
            });
        //dump($dre_montado4);

        $dre_montado3 = [];
        //$dre_nivel3 = [];
        $items_nivel3 = $documento_items
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado3, &$dre_all) {
                foreach ($item as $item) {
                    $plano_cta = $this->getPlanoCtaPai($item->first()['parent']);
                    $dre_montado3[$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'),
                        'itens_count'   => $item->count(),
                    ];
                    $dre_all[$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $this->formatNumber($item->sum('valor_sum')),
                        'itens_count'   => $item->count(),
                    ];
                }
            });
        //dump($dre_montado3);

        $dre_montado2 = [];
        // Pega o montado no nível três e usa o método 'flatten' para reduzir um nível/dimensão do array.
        // com isso posso aplicar a mesma estrutura do nível 3 no nível 2.
        $items_nivel2 = collect($dre_montado3)->flatten(1)
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado2, &$dre_all) {
                foreach ($item as $item) {
                    $plano_cta = $this->getPlanoCtaPai($item->first()['parent']);
                    $dre_montado2[$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'),
                        'itens_count'   => $item->count(),
                    ];
                    $dre_all[$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $this->formatNumber($item->sum('valor_sum')),
                        'itens_count'   => $item->count(),
                    ];
                }
            });
        //dump($dre_montado2);

        $dre_montado1 = [];
        // Pega o montado no nível três e usa o método 'flatten' para reduzir um nível/dimensão do array.
        // com isso posso aplicar a mesma estrutura do nível 3 no nível 2.
        $items_nivel1 = collect($dre_montado2)->flatten(1)
            ->groupBy(['parent', 'month'])
            ->each(function ($item) use (&$dre_montado1, &$dre_all) {
                foreach ($item as $item) {
                    $plano_cta = $this->getPlanoCtaPai($item->first()['parent']);
                    $dre_montado1[(string)$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $item->sum('valor_sum'),
                        'itens_count'   => $item->count(),
                    ];
                    $dre_all[(string)$plano_cta->codigo][$item->first()['month']] = [
                        'parent'        => $plano_cta->parent, // Pega o parent obtido no PlanoCtaItem, e não o parent da linha atual.
                        'codigo'        => $plano_cta->codigo,
                        'month'         => $item->first()['month'],
                        'valor_sum'     => $this->formatNumber($item->sum('valor_sum')),
                        'itens_count'   => $item->count(),
                    ];
                }
            });

        // NOTE: O método sortKeys() vai ordenar a coleção de dados pelo seu índex, no caso o código.
        $report_all = collect($dre_all)->sortKeys();

        /**
         * Reorganiza os dados: agrupa/ordena.
         */
        $report = [];
        $documento_items->each(function ($item) use (&$report) {
            $report[$item->codigo][$item->month] = [
                'valor_sum' => $item->valor_sum,
                'itens_count' => $item->invoices,
            ];
        });
        //dump($report);


        return view('test-dreanual', compact('report', 'codigos', 'meses', 'report_all'));
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

    public static function currencyToDb(string|float $number, int $decimals = 2): string
    {
        // Primeiro: no 'str_replace' da direita, substitui indicador de milhar(.) por vazio.
        // Depois: no 'str_replace' da esquerda, substitui indicador de decimal(,) por ponto.
        $number = str_replace(',', '.', str_replace('.', '', $number));
        $number = number_format((float)$number, $decimals, '.', '');

        return $number;
    }
    public static function currencyGetDb(string|float $number, int $decimals = 2, string $thousandSeparator = ''): string
    {
        // Primeiro: no 'str_replace' da direita, substitui indicador de milhar(.) por vazio.
        // Depois: no 'str_replace' da esquerda, substitui indicador de decimal(,) por ponto.
        //$number = str_replace(',', '.', str_replace('.', '', $number));
        $number = number_format((float)$number, $decimals, ',', $thousandSeparator);

        return $number;
    }
}
