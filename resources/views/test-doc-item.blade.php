<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="flex gap-3 px-5 py-2 flex-wrap">
                    @foreach ($documento_classes as $item)
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [$tipo_id, $item->id]) }}">{{ $item->nome }}</a>
                    @endforeach
                </div>

                <div class="flex gap-3 px-5 py-2 flex-wrap">
                    {{-- TIPO --}}
                    <div class="flex gap-3 flex-wrap">
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                1,
                                isset($classe_id) ? '&classe_id=' . $classe_id : '',
                                isset($mes_id) ? '&mes_id=' . $mes_id : '',
                                isset($ano_id) ? '&ano_id=' . $ano_id : '',
                            ]) }}">Cta.
                            a pagar
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                2,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">Cta.
                            a receber
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                3,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">Cta.
                            Paga
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                4,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">Cta.
                            Recebida
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                5,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">Tarifa
                            bancária
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                6,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">Mov.
                            Titular
                        </a>
                    </div>
                    {{-- CLEAR --}}
                    <div class="flex gap-3 flex-wrap">
                        <a class="btn btn-warning px-2 py-1" href="{{ route('doc.item', [$tipo_id, '']) }}">CLEAR
                        </a>
                    </div>
                </div>


                <div class="flex gap-3 px-5 py-2 flex-wrap">
                    {{-- MESES --}}
                    <div class="flex gap-3 flex-wrap">
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=1',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">JAN</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=2',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">FEV
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=3',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">MAR
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=4',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">ABR
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=5',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">MAI
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=6',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">JUN
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=7',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">JUL
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=8',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">AGO
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=9',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">SET
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=10',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">OUT
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=11',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">NOV
                        </a>
                        <a class="btn px-2 py-1"
                            href="{{ route('doc.item', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                'mes_id=12',
                                isset($ano_id) ? 'ano_id=' . $ano_id : '',
                            ]) }}">DEZ
                        </a>
                    </div>
                    {{-- ANOS --}}
                    <div class="flex gap-3 px-5 flex-wrap">
                        <a class="btn px-2 py-1"
                            href="{{ route('previsao', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                'ano_id=2022',
                            ]) }}">2022</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('previsao', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                'ano_id=2023',
                            ]) }}">2023</a>
                        <a class="btn px-2 py-1"
                            href="{{ route('previsao', [
                                $tipo_id,
                                isset($classe_id) ? 'classe_id=' . $classe_id : '',
                                isset($mes_id) ? 'mes_id=' . $mes_id : '',
                                'ano_id=2024',
                            ]) }}">2024</a>
                    </div>
                </div>




                <table class="table">
                    <thead>
                        <tr>
                            <th class="">#</th>
                            <th class="">Pessoa</th>
                            <th class="">Classe</th>
                            <th class="">Codigo</th>
                            <th class="">Vencimento</th>
                            <th class="">Categoria</th>
                            <th class="">Nome categoria</th>
                            <th class="">Descrição</th>
                            <th class="">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($doc_items as $documento)
                            <tr>

                                <th class="">{{ $documento->id }}</th>
                                {{-- <td>{{$documento->pessoa}}</td> --}}
                                <td>{{ $documento->toDocumento->toPessoa->nome_razao }}</td>
                                <td>{{ $documento->doc_classe }}</td>
                                <td>{{ $documento->toDocumento->codigo }}</td>
                                <td>{{ $documento->toDocumento->data_venc->format('d/m/Y') }}</td>
                                <td>{{ $documento->to_plano_cta_item_codigo }}</td>
                                <td>{{ $documento->to_plano_cta_item_nome }}</td>
                                <td>{{ $documento->descricao }}</td>
                                <td>{{ $documento->valor }}</td>
                                {{-- <td>{{$documento->to_documento_tipo_nome}}</td>
                            <td>{{$documento->codigo}}</td> --}}
                            </tr>
                        @endforeach

                </table>


            </div>
        </div>
    </div>
</x-app-layout>
