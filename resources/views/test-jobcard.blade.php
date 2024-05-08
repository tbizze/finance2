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
                        <th class="">Month</th>
                        @foreach ($job_comp_codes as $job_comp_code)
                            <th class="">Job Amount {{ $job_comp_code }}</th>
                        @endforeach
                        @foreach ($job_comp_codes as $job_comp_code)
                            <th class="">Job Count {{ $job_comp_code }}</th>
                        @endforeach
                    </thead>
                    @foreach ($report as $month => $values)
                        <tr>
                            <th class="">{{ $month }}</th>
                            @foreach ($job_comp_codes as $job_comp_code)
                                <td>{{ $report[$month][$job_comp_code]['amount'] ?? '0' }}</td>
                            @endforeach
                            @foreach ($job_comp_codes as $job_comp_code)
                                <td>{{ $report[$month][$job_comp_code]['count'] ?? '0' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </table>

            </div>
        </div>
    </div>
</x-app-layout>
