<table>
    <thead>
        <!-- Judul Laporan -->
        <tr>
            <th colspan="{{ 4 + count($headerHafalan) }}" style="font-size: 14px; font-weight: bold; text-align: center;">
                REKAP HAFALAN SANTRI PER MUSTAHIQ
            </th>
        </tr>
        <tr>
            <th colspan="{{ 4 + count($headerHafalan) }}" style="font-size: 11px; font-weight: bold; text-align: center; color: #595959;">
                Tahun Ajaran: {{ $tahunAjaran }} | {{ $filterLabel }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 4 + count($headerHafalan) }}"></th>
        </tr>
        
        <!-- Header Tabel -->
        <tr style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">No</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">NOIN</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: left; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Nama</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: left; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Unit Sekolah</th>
            @foreach($headerHafalan as $hafalan)
                @php
                    $bgColor = $hafalan['kriteria'] === 'Wajib' ? '#FCE4D6' : ($hafalan['kriteria'] === 'Sunnah' ? '#E2EFDA' : '#FFF2CC');
                @endphp
                <th style="{{ 'border: 1px solid #000000; font-weight: bold; text-align: center; background-color: ' . $bgColor . '; color: #000000; vertical-align: middle;' }}">
                    {{ $hafalan['nama_hafalan'] }} ({{ $hafalan['kriteria'] }})
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($hasilRekap as $i => $row)
            <tr>
                <td style="border: 1px solid #BFBFBF; text-align: center; vertical-align: middle;">{{ $i + 1 }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: center; vertical-align: middle; font-family: monospace;">{{ $row['noin'] }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: left; vertical-align: middle;">{{ $row['nama'] }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: left; vertical-align: middle;">{{ $row['unit_label'] }}</td>
                @foreach($headerHafalan as $hafalan)
                    @php
                        $isHafal = $row['hafalan'][$hafalan['id']] ?? false;
                        $cellColor = $isHafal ? '#10B981' : '#6B7280';
                        $cellWeight = $isHafal ? 'bold' : 'normal';
                    @endphp
                    <td style="{{ 'border: 1px solid #BFBFBF; text-align: center; vertical-align: middle; color: ' . $cellColor . '; font-weight: ' . $cellWeight . ';' }}">
                        @if($isHafal)
                            ✓
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
