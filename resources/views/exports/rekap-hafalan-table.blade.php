<table>
    <thead>
        <!-- Judul Laporan -->
        <tr>
            <th colspan="{{ 3 + ($jenisPendidikan === 'kurikulum' ? 1 : 0) + count($headerHafalan) }}" style="font-size: 14px; font-weight: bold; text-align: center;">
                REKAP HAFALAN SANTRI
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + ($jenisPendidikan === 'kurikulum' ? 1 : 0) + count($headerHafalan) }}" style="font-size: 11px; font-weight: bold; text-align: center; color: #595959;">
                Filter: {{ $filterLabel }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + ($jenisPendidikan === 'kurikulum' ? 1 : 0) + count($headerHafalan) }}"></th>
        </tr>
        
        <!-- Header Tabel -->
        <tr style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">No</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">NOIN</th>
            <th style="border: 1px solid #000000; font-weight: bold; text-align: left; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Nama</th>
            @if($jenisPendidikan === 'kurikulum')
                <th style="border: 1px solid #000000; font-weight: bold; text-align: left; background-color: #1F4E78; color: #FFFFFF; vertical-align: middle;">Madin</th>
            @endif
            @foreach($headerHafalan as $hafalan)
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; background-color: {{ $hafalan['kriteria'] === 'Wajib' ? '#FCE4D6' : ($hafalan['kriteria'] === 'Sunnah' ? '#E2EFDA' : '#FFF2CC') }}; color: #000000; vertical-align: middle;">
                    {{ $hafalan['tkt_label'] }} K{{ $hafalan['mkls'] }} - {{ $hafalan['nama_hafalan'] }} ({{ $hafalan['kriteria'] }})
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
                @if($jenisPendidikan === 'kurikulum')
                    <td style="border: 1px solid #BFBFBF; text-align: left; vertical-align: middle;">{{ $row['madin_label'] ?? '-' }}</td>
                @endif
                @foreach($headerHafalan as $hafalan)
                    <td style="border: 1px solid #BFBFBF; text-align: center; vertical-align: middle; color: {{ ($row['hafalan'][$hafalan['id']] ?? false) ? '#10B981' : '#6B7280' }}; font-weight: {{ ($row['hafalan'][$hafalan['id']] ?? false) ? 'bold' : 'normal' }};">
                        @if($row['hafalan'][$hafalan['id']] ?? false)
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
