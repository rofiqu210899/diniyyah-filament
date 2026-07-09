<table>
    <thead>
        <!-- Judul Laporan -->
        <tr>
            <th colspan="12" style="font-size: 16px; font-weight: bold; text-align: center;">
                REKAPITULASI HAFALAN TINGKAT {{ $tktName }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12px; font-weight: bold; text-align: center; color: #595959;">
                Tahun Ajaran: {{ $tahunAjaran }}
            </th>
        </tr>
        <tr>
            <th colspan="12"></th>
        </tr>
    </thead>
</table>

@foreach($rowsGroupedByGrade as $mkls => $classList)
    @php
        $classHafalans = $hafalansGroupedByGrade->get($mkls, collect());
        $colCount = 13 + $classHafalans->count();
    @endphp
    
    <table>
        <thead>
            <!-- Nama Tingkat Kelas Kelompok -->
            <tr>
                <th colspan="{{ $colCount }}" style="font-size: 14px; font-weight: bold; text-align: left; background-color: #D9E1F2; color: #000000; vertical-align: middle; padding: 5px;">
                    KELAS {{ $mkls }} {{ $tktName }}
                </th>
            </tr>
            <!-- Header Kolom Tabel Kelas Kelompok -->
            <tr style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">No</th>
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Kelas</th>
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Jenis Kelamin</th>
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Nama Mustahiq</th>
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Jumlah Santri</th>
                
                <!-- Kolom Hafalan Khusus Kelas Tingkatan Ini -->
                @foreach($classHafalans as $hafalan)
                    <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: {{ $hafalan->kriteria == 'Wajib' ? '#FCE4D6' : ($hafalan->kriteria == 'Sunnah' ? '#E2EFDA' : '#FFF2CC') }}; color: #000000; vertical-align: middle;">
                        {{ $hafalan->nama_hafalan }}<br>({{ $hafalan->kriteria }})
                    </th>
                @endforeach
                
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">Rata-rata</th>
                
                <th colspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">hafalan Wajib</th>
                <th colspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">hafalan Sunnah</th>
                <th colspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">hafalan Wisuda</th>
                
                <th rowspan="2" style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">Prosentase total</th>
            </tr>
            <tr style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000;">
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">jumlah</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">prosentase</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">jumlah</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">prosentase</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">jumlah</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #2F5597; color: #FFFFFF; vertical-align: middle;">prosentase</th>
            </tr>
        </thead>
        <tbody>
            @foreach($classList as $index => $row)
                @php
                    $isLastInGroup = ($index === count($classList) - 1);
                    $borderStyle = 'border-left: 1px solid #BFBFBF; border-right: 1px solid #BFBFBF; border-top: 1px solid #BFBFBF;';
                    if ($isLastInGroup) {
                        $borderStyle .= ' border-bottom: 2px solid #000000;';
                    } else {
                        $borderStyle .= ' border-bottom: 1px solid #BFBFBF;';
                    }
                @endphp
                <tr>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td style="{{ $borderStyle }} font-weight: bold; vertical-align: middle;">{{ $row['kelas_label'] }}</td>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['jk_label'] }}</td>
                    <td style="{{ $borderStyle }} vertical-align: middle;">{{ $row['mustahiq'] }}</td>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['jumlah'] }}</td>
                    
                    <!-- Nilai Progres Hafalan Santri -->
                    @foreach($classHafalans as $hafalan)
                        @php
                            $val = $row['hafalan_completions'][$hafalan->id] ?? 0;
                        @endphp
                        <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">
                            {{ $val }}
                        </td>
                    @endforeach
                    
                    <td style="{{ $borderStyle }} text-align: center; font-weight: bold; vertical-align: middle;">{{ $row['rata_rata'] }}</td>
                    
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['wajib_jumlah'] }}</td>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['wajib_prosentase'] }}%</td>
                    
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['sunnah_jumlah'] }}</td>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['sunnah_prosentase'] }}%</td>
                    
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['wisuda_jumlah'] }}</td>
                    <td style="{{ $borderStyle }} text-align: center; vertical-align: middle;">{{ $row['wisuda_prosentase'] }}%</td>
                    
                    <td style="{{ $borderStyle }} text-align: center; font-weight: bold; background-color: #F2F2F2; vertical-align: middle;">
                        {{ $row['prosentase'] }}%
                    </td>
                </tr>
            @endforeach
            <!-- Spacer kosong setelah setiap tabel tingkatan kelas -->
            <tr>
                <td colspan="{{ $colCount }}"></td>
            </tr>
            <tr>
                <td colspan="{{ $colCount }}"></td>
            </tr>
        </tbody>
    </table>
@endforeach
