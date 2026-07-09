@php
    $tktName = $data['tktName'];
    $tahunAjaran = $data['tahunAjaran'];
    $hafalansGroupedByGrade = $data['hafalansGroupedByGrade'];
    $rowsGroupedByGrade = $data['rowsGroupedByGrade'];
@endphp

<div class="space-y-2 text-center pb-4">
    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">
        REKAPITULASI HAFALAN TINGKAT {{ $tktName }}
    </h3>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Tahun Ajaran: {{ $tahunAjaran }}
    </p>
</div>

@if(empty($rowsGroupedByGrade))
    <div class="p-6 text-center text-gray-500 bg-gray-50 dark:bg-gray-850 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
        Tidak ada data kelas mustahiq pada tingkatan ini untuk tahun ajaran terpilih.
    </div>
@else
    <div class="space-y-8">
        @foreach($rowsGroupedByGrade as $mkls => $classList)
            @php
                $classHafalans = $hafalansGroupedByGrade->get($mkls, collect());
                $colCount = 13 + $classHafalans->count();
            @endphp
            
            <div class="space-y-2">
                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-900">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <!-- Nama Tingkat Kelas Kelompok -->
                            <tr class="bg-blue-50 dark:bg-blue-950/40">
                                <th colspan="{{ $colCount }}" class="px-4 py-3 font-bold text-sm text-blue-900 dark:text-blue-200 border-b border-gray-200 dark:border-gray-700">
                                    KELAS {{ $mkls }} {{ $tktName }}
                                </th>
                            </tr>
                            <!-- Header Kolom Tabel Kelas Kelompok -->
                            <tr class="font-bold text-center border-b border-gray-300 dark:border-gray-700 divide-x divide-gray-200 dark:divide-gray-800">
                                <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold w-10 border-r border-gray-200 dark:border-gray-700" style="background-color: #f3f4f6; color: #000000;">No</th>
                                <th rowspan="2" class="px-3 py-3 text-left align-middle font-bold min-w-[100px] border-r border-gray-200 dark:border-gray-700" style="background-color: #f3f4f6; color: #000000;">Kelas</th>
                                <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold min-w-[110px] border-r border-gray-200 dark:border-gray-700" style="background-color: #f3f4f6; color: #000000;">Jenis Kelamin</th>
                                <th rowspan="2" class="px-3 py-3 text-left align-middle font-bold min-w-[160px] border-r border-gray-200 dark:border-gray-700" style="background-color: #f3f4f6; color: #000000;">Nama Mustahiq</th>
                                <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold w-24 border-r border-gray-200 dark:border-gray-700" style="background-color: #f3f4f6; color: #000000;">Jumlah Santri</th>
                                
                                <!-- Kolom Hafalan Khusus Kelas Tingkatan Ini (Wajib di Kiri) -->
                                @foreach($classHafalans as $hafalan)
                                    <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700 min-w-[110px]" style="background-color: {{ $hafalan->kriteria === 'Wajib' ? '#FCE4D6' : ($hafalan->kriteria === 'Sunnah' ? '#E2EFDA' : '#FFF2CC') }}; color: #000000;">
                                        <div class="leading-tight">{{ $hafalan->nama_hafalan }}</div>
                                        <div class="text-[9px] font-semibold opacity-75">({{ $hafalan->kriteria }})</div>
                                    </th>
                                @endforeach
                                
                                <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold w-20 border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">Rata-rata</th>
                                
                                <th colspan="2" class="px-2 py-3 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">hafalan Wajib</th>
                                <th colspan="2" class="px-2 py-3 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">hafalan Sunnah</th>
                                <th colspan="2" class="px-2 py-3 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">hafalan Wisuda</th>
                                
                                <th rowspan="2" class="px-2 py-3 text-center align-middle font-bold w-24" style="background-color: #e5e7eb; color: #000000;">Prosentase total</th>
                            </tr>
                            <tr class="font-bold text-center border-b border-gray-300 dark:border-gray-700 divide-x divide-gray-200 dark:divide-gray-800">
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">jumlah</th>
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">prosentase</th>
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">jumlah</th>
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">prosentase</th>
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">jumlah</th>
                                <th class="px-2 py-2 text-center align-middle font-bold border-r border-gray-200 dark:border-gray-700" style="background-color: #e5e7eb; color: #000000;">prosentase</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($classList as $index => $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition divide-x divide-gray-100 dark:divide-gray-800">
                                    <td class="px-2 py-2.5 text-center font-medium text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2.5 font-bold text-gray-900 dark:text-white">{{ $row['kelas_label'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-gray-700 dark:text-gray-300">{{ $row['jk_label'] }}</td>
                                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">{{ $row['mustahiq'] }}</td>
                                    <td class="px-2 py-2.5 text-center font-semibold text-gray-900 dark:text-white">{{ $row['jumlah'] }}</td>
                                    
                                    <!-- Nilai Progres Hafalan Santri -->
                                    @foreach($classHafalans as $hafalan)
                                        @php
                                            $val = $row['hafalan_completions'][$hafalan->id] ?? 0;
                                        @endphp
                                        <td class="px-2 py-2.5 text-center font-bold text-gray-900 dark:text-white bg-gray-50/20 dark:bg-gray-950/5">
                                            {{ $val }}
                                        </td>
                                    @endforeach
                                    
                                    <td class="px-2 py-2.5 text-center font-bold text-blue-600 dark:text-blue-400 bg-blue-50/30 dark:bg-blue-950/10">{{ $row['rata_rata'] }}</td>
                                    
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['wajib_jumlah'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['wajib_prosentase'] }}%</td>
                                    
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['sunnah_jumlah'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['sunnah_prosentase'] }}%</td>
                                    
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['wisuda_jumlah'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-gray-800 dark:text-gray-200">{{ $row['wisuda_prosentase'] }}%</td>
                                    
                                    <td class="px-2 py-2.5 text-center font-bold text-emerald-600 dark:text-emerald-400 bg-gray-50 dark:bg-gray-950/20">
                                        {{ $row['prosentase'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endif
