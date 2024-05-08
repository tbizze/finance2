<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoBaixa;
use App\Models\DocumentoItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class HomeConfereController extends Controller
{
    private int $ano_id = 0;
    private int $mes_id = 0;
    private int $cta_id = 0;
    // Método para obter dados de conferência de DocumentoBaixa.
    public function confAll(Request $request)
    {
        // #########################################
        // Prepara filtros, com base na URL
        if ($request->has('cta_id')) {
            $this->cta_id = (int)$request->query('cta_id');
        }
        if ($request->has('mes_id')) {
            $this->mes_id = (int)$request->query('mes_id');
        }
        if ($request->has("ano_id")) {
            $this->ano_id = (int)$request->query('ano_id');
        }

        $documentos = $this->confDocumento($this->cta_id, $this->mes_id, $this->ano_id,);
        $documento_vencidos = $this->confDocumento($this->cta_id, $this->mes_id, $this->ano_id, [3]);
        $documento_pagos = $this->confDocumento($this->cta_id, $this->mes_id, $this->ano_id, [1]);
        $documento_rel_baixados = $this->confDocumento($this->cta_id, $this->mes_id, $this->ano_id, [1], true);
        $documento_baixas = $this->confDocumentoBaixa($this->cta_id, $this->mes_id, $this->ano_id);
        $documento_items = $this->confDocumentoItem($this->cta_id, $this->mes_id, $this->ano_id);
        $documento_item_pagos = $this->confDocumentoItem($this->cta_id, $this->mes_id, $this->ano_id, [1]);

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#E3371E">DOCUMENTO ITENS:</div>';
        echo '<table style="padding:0px 10px 5px 5px;"><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_items as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#E3371E">DOCUMENTO ITENS -- STATUS PAGO:</div>';
        echo '<table style="padding:0px 10px 5px 5px;"><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_item_pagos as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#103778">DOCUMENTO BAIXAS:</div>';
        echo '<table><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_baixas as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#0593A2">DOCUMENTOS -- STATUS PAGO (c/ baixa):</div>';
        echo '<table><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_rel_baixados as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#0593A2">DOCUMENTOS -- STATUS PAGO:</div>';
        echo '<table><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_pagos as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#0593A2">DOCUMENTOS -- STATUS VENCIDO:</div>';
        echo '<table><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documento_vencidos as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<div style="padding:10px 5px 5px 5px; font-weight: bold; color:#F2668B">DOCUMENTOS -- TODOS:</div>';
        echo '<table><tr>';
        echo '<td>Ano</td><td>Itens</td><td>Soma</td>';
        echo '</tr>';
        foreach ($documentos as $item) {
            echo '<tr>';
            echo '<td>' . $item['ano'] . '</td>';
            echo '<td style="text-align: center;">' . $item['itens_contado'] . '</td>';
            echo '<td>' . $this->formatNumber($item['valor_somado']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        /* echo '<BR>';
        echo 'Soma VENCIDO + PAGO: ' . $this->formatNumber($documento_vencidos[0]['valor_somado'] + $documento_pagos[0]['valor_somado']);
        echo ' (' . $documento_vencidos[0]['itens_contado'] + $documento_pagos[0]['itens_contado'] . ' itens)'; */

        if (count($documento_vencidos) > 0) {
            echo '<BR>';
            echo 'Soma VENCIDO + PAGO: ';
            foreach ($documentos as $key => $item) {
                try {
                    echo '<br>=>' . $item['ano'] . ': ';
                    if (isset($documento_vencidos[$key]['itens_contado'])) {
                        echo $this->formatNumber($documento_vencidos[$key]['valor_somado'] + $documento_pagos[$key]['valor_somado']);
                        echo ' (' . $documento_vencidos[$key]['itens_contado'] + $documento_pagos[$key]['itens_contado'] . ' itens)';
                    } else {
                        echo $this->formatNumber($documento_pagos[$key]['valor_somado']);
                        echo ' (' . $documento_pagos[$key]['itens_contado'] . ' itens)';
                    }
                } catch (\Exception $e) {
                    //throw $th;
                    echo $e->getMessage();
                }
            }
        }
        /* dump($documentos->toArray());
        dump($documento_baixas->toArray());
        dump($documento_items->toArray()); */
    }

    // Método para obter dados de conferência de Documento.
    public function confDocumento(?int $cta_id = null, ?int $mes_id, ?int $ano_id, ?array $status_id = null, ?bool $conf_baixa = null)
    {
        /* Busca Documento, filtrando pelo ANO/MÊS, caso tenha sido passado na URL.
           SELECT: Retorna 'itens_contado'...
           WHERE: Somente aqueles com status = 1 [PAGO].
           GROUP BY: Por ano -> referência 'data_venc'.
         */
        //dd($mes_id, $ano_id);

        $dados = Documento::query()
            ->selectRaw('COUNT(documentos.id) as itens_contado')
            ->selectRaw('SUM(documento_items.valor) as valor_somado')
            ->selectRaw('YEAR(data_venc) AS ano')
            ->join('documento_items', 'documento_items.documento_id', '=', 'documentos.id')
            ->when($conf_baixa, function ($query) {
                $query->join('documento_baixas', 'documento_baixas.documento_id', '=', 'documentos.id');
            })
            // Wheres
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('data_venc', $value); // Filtra pelo mês ==> DATA/VENCIMENTO
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('data_venc', $value); // Filtra pelo ano ==> DATA/VENCIMENTO
            })
            ->when($status_id, function ($query, $value) {
                $query->whereIn('documento_status_id', $value); // Filtra pelo ano ==> DATA/VENCIMENTO
            })
            // NOTE: Qdo. comparado c/ DocumentoBaixa, deve filtrar como abaixo: somente PAGO.
            //->where('documento_status_id', 1) // Filtra: somente pago.
            // GroupBy
            ->groupByRaw('YEAR(data_venc)')
            ->get();
        //->toSql();
        //echo 'Documentos: ';
        //dd($dados);
        //dump($dados->toArray());

        return $dados;
    }

    // Método para obter dados de conferência de DocumentoBaixa.
    public function confDocumentoBaixa($cta_id = null, $mes_id = null, $ano_id = null)
    {
        /* Busca DocumentoBaixa, filtrando pelo ANO/MÊS, caso tenha sido passado na URL.
           SELECT: Retorna 'itens_contado'...
           WHERE: ...
           GROUP BY: Por ano -> referência 'dt_baixa'.
         */
        $dados = DocumentoBaixa::query()
            ->selectRaw('COUNT(id) as itens_contado')
            ->selectRaw('SUM(valor_baixa) as valor_somado')
            ->selectRaw('YEAR(dt_baixa) AS ano')
            ->when($ano_id, function ($query, $value) {
                $query->whereYear('dt_baixa', $value); // Filtra pelo mês
            })
            ->when($mes_id, function ($query, $value) {
                $query->whereMonth('dt_baixa', $value); // Filtra pelo ano
            })
            ->groupByRaw('YEAR(dt_baixa)')
            //->toSql();
            ->get();
        //echo 'Documentos Baixa';
        //dump($itens_resumo);
        //dump($itens_resumo->toArray());

        return $dados;
    }

    // Método para obter dados de conferência de DocumentoItem.
    public function confDocumentoItem($cta_id = null, $mes_id = null, $ano_id = null, $status_id = null)
    {
        /* Busca DocumentoBaixa, filtrando pelo ANO/MÊS, caso tenha sido passado na URL.
           Como DocumentoItem não tem data, será usada 'data_vencimento', pela relação.
           Como a data é de fora da tabela principal, usamos o recurso JOIN p/ unir as tabelas.
           SELECT: Retorna 'itens_contado'...
           WHERE: ...
           GROUP BY: Por ano -> referência 'dt_baixa'.
         */
        $dados = DocumentoItem::query()
            ->selectRaw('COUNT(documento_items.id) as itens_contado')
            ->selectRaw('SUM(valor) as valor_somado')
            ->selectRaw('YEAR(documentos.data_venc) AS ano')
            ->join('documentos', 'documento_items.documento_id', '=', 'documentos.id')
            ->whereHas('toDocumento', function ($query) use ($mes_id, $ano_id) {
                $query->when($ano_id, function ($query, $value) {
                    $query->whereYear('data_venc', $value); // Filtra pelo mês
                });
                $query->when($mes_id, function ($query, $value) {
                    $query->whereMonth('data_venc', $value); // Filtra pelo ano
                });
            })
            ->when($status_id, function ($query, $value) {
                $query->whereIn('documento_status_id', $value); // Filtra pelo mês
            })
            ->groupByRaw('YEAR(documentos.data_venc)')->get();
        //echo 'Documentos Itens';
        //dump($itens_resumo->toArray());

        return $dados;
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
