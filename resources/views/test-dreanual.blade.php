<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

                <table class="table">
                    <thead>
                        <th class="">Categorias</th>
                        @foreach ($meses as $mes)
                            <th class="text-right">{{ $mes }}</th>
                        @endforeach
                        {{-- @foreach ($job_comp_codes as $job_comp_code)
                            <th class="">Job Count {{ $job_comp_code }}</th>
                        @endforeach --}}
                    </thead>
                    @foreach ($report as $key => $values)
                        <tr>
                            <th class="">{{ $key }}</th>
                            @foreach ($meses as $mes)
                                {{-- <td>{{ $report[$month][$job_comp_code]['valor_sum'] ?? '0' }}</td> --}}
                                <td class="text-right">{{ $report[$key][$mes]['valor_sum'] ?? '--' }} </td>
                            @endforeach
                            {{-- @foreach ($meses as $mes)
                                <td>{{ $report[$key][$mes]['itens_count'] ?? '0' }}</td>
                            @endforeach --}}
                        </tr>
                    @endforeach
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
