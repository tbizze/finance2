<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class=" mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex gap-3 px-5 py-2 flex-wrap">
                    {{-- MESES --}}
                    <div class="flex gap-3 flex-wrap">
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=1', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">JAN</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=2', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">FEV
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=3', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">MAR
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=4', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">ABR
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=5', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">MAI
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=6', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">JUN
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=7', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">JUL
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=8', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">AGO
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=9', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">SET
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=10', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">OUT
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=11', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">NOV
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', ['mes_id=12', isset($ano_id) ? 'ano_id=' . $ano_id : '']) }}">DEZ
                        </a>
                    </div>
                    {{-- ANOS --}}
                    <div class="flex gap-3 px-5 flex-wrap">
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', [isset($mes_id) ? 'mes_id=' . $mes_id : '', 'ano_id=2022']) }}">2022</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', [isset($mes_id) ? 'mes_id=' . $mes_id : '', 'ano_id=2023']) }}">2023</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('dre.anual2', [isset($mes_id) ? 'mes_id=' . $mes_id : '', 'ano_id=2024']) }}">2024</a>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        {{-- Cabeçalho da coluna c/ códigos das categoria --}}
                        <th class="bg-orange-50">Categorias</th>
                        {{-- Loop p/ exibir cabeçalho da colunas dos meses em que há dados passados --}}
                        @foreach ($meses as $mes)
                            <th class="text-right bg-orange-50">{{ $mes }}</th>
                        @endforeach
                        <th class="text-right bg-blue-50">Total</th>
                    </thead>
                    @foreach ($report_all as $key => $values)
                        <tr>
                            {{-- Coluna dos nosmes das categorias --}}
                            <th class=" py-1">{{ $key }} </th>
                            {{-- Loop p/ exibir em cada coluna, o valor somado daquela linha/categoria --}}
                            @foreach ($meses as $mes)
                                <td class="text-right py-1">{{ $report_all[$key][$mes]['valor_sum'] ?? '--' }}
                                </td>
                            @endforeach
                            {{-- @foreach ($meses as $mes)
                                <td>{{ $report_all[$key][$mes]['itens_count'] ?? '0' }}</td>
                            @endforeach --}}
                            {{-- Coluna do total anual. Obtem dados sem o agrupamento mensal --}}
                            <td class="text-right bg-blue-50 py-1">{{ $report_all_total[$key]['valor_sum'] ?? '--' }}
                            </td>
                        </tr>
                    @endforeach
                </table>


            </div>
        </div>
    </div>
</x-app-layout>
