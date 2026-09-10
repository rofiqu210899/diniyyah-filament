<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Tanda Penghargaan' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        @page {
            size: {{ $cssPaperSize ?? '215mm 330mm' }};
            margin: 0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #525659;
            font-family: '{{ $setting->font_family ?? 'Times New Roman' }}', 'Times New Roman', Times, serif;
            color: #000000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Screen toolbar */
        .no-print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            background: #1e293b;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            font-family: 'Roboto', sans-serif;
        }

        .toolbar-title {
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-actions {
            display: flex;
            gap: 12px;
        }

        .btn-action {
            background: #198754;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-action:hover {
            background: #157347;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #475569;
        }

        .btn-secondary:hover {
            background: #334155;
        }

        .certificate-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 70px 0 40px 0;
            gap: 30px;
        }

        /* Single Page Sheet */
        .certificate-sheet {
            width: {{ $paperWidthMm ?? 215 }}mm;
            height: {{ $paperHeightMm ?? 330 }}mm;
            background-color: {{ $setting->bg_color ?? '#ffffff' }};
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            page-break-after: always;
            break-after: page;
        }

        .certificate-sheet:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        /* Content Margins Container */
        .sheet-inner {
            position: absolute;
            top: {{ $setting->margin_top ?? 50 }}mm;
            right: {{ $setting->margin_right ?? 25 }}mm;
            bottom: {{ $setting->margin_bottom ?? 20 }}mm;
            left: {{ $setting->margin_left ?? 25 }}mm;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header / Judul */
        .title-block {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title {
            font-size: {{ $setting->font_size_title ?? 20 }}pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .doc-nomor-wrap {
            display: inline-block;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2px;
            margin-top: 1px;
        }

        .doc-nomor {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        /* 2. Biodata Table */
        .biodata-section {
            margin-top: 4px;
            margin-bottom: 16px;
        }

        .biodata-table {
            width: 100%;
            border-collapse: collapse;
            font-size: {{ $setting->font_size_body ?? 12 }}pt;
            line-height: {{ $setting->line_spacing ?? 1.2 }};
        }

        .biodata-table tr td {
            padding: 2.5px 0;
            vertical-align: top;
        }

        .col-label {
            width: 150px;
            white-space: nowrap;
        }

        .col-sep {
            width: 20px;
            text-align: center;
        }

        .col-val {
            font-weight: normal;
        }

        .val-nama {
            font-weight: bold;
            text-transform: uppercase;
        }

        .val-orangtua {
            text-transform: uppercase;
        }

        .val-kelas {
            text-transform: uppercase;
        }

        /* 3. Middle Section: Photo & TTD 1 (PKM Muhafadhoh) */
        .middle-section {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            position: relative;
            min-height: 145px;
        }

        .photo-box-wrap {
            position: absolute;
            left: 200px;
            top: 6px;
        }

        .photo-box {
            width: 30mm;
            height: 40mm;
            border: 1px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10pt;
            color: #333333;
            line-height: 1.3;
        }

        .ttd1-wrap {
            width: 235px;
            text-align: center;
            font-size: {{ $setting->font_size_footer ?? 11 }}pt;
        }

        .date-hijri {
            margin-bottom: 1px;
        }

        .date-masehi {
            border-bottom: 1px solid #000000;
            display: inline-block;
            padding-bottom: 1px;
            margin-bottom: 8px;
        }

        .ttd-jabatan {
            margin-bottom: 2px;
        }

        .ttd-spacer {
            height: 80px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ttd-signature-img {
            max-height: 48px;
            max-width: 140px;
            object-fit: contain;
        }

        .ttd-nama {
            font-weight: bold;
            text-transform: uppercase;
    
        }

        /* 4. Bottom Section: Mengetahui & 2 Signers */
        .bottom-section {
            margin-top: 60px;
            width: 100%;
        }

        .mengetahui-label {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 12px;
        }

        .bottom-signers-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signer-col {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: {{ $setting->font_size_footer ?? 11 }}pt;
            padding: 0 0px;
        }

        .stempel-overlay {
            position: absolute;
            max-height: 70px;
            max-width: 70px;
            opacity: 0.85;
            left: 18%;
            top: -10px;
            z-index: 5;
            pointer-events: none;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none !important;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .certificate-container {
                padding: 0 !important;
                gap: 0 !important;
            }

            .certificate-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Toolbar -->
    <div class="no-print-toolbar">
        <div class="toolbar-title">
            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>{{ $pageTitle ?? 'Tanda Penghargaan' }} ({{ count($students) }} Sertifikat)</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn-action">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Simpan PDF
            </button>
            <a href="javascript:window.close()" class="btn-action btn-secondary">Tutup</a>
        </div>
    </div>

    <div class="certificate-container">
        @foreach($students as $index => $item)
            @php
                $santri = $item['santri'];
                $mustahiq = $item['mustahiq'];
                $tahunAjaranNama = $item['tahun_ajaran_nama'];
                
                // Format Tempat / Tgl Lahir
                $ttlFormatted = '-';
                if ($santri->tl && $santri->tlhr && $santri->bln && $santri->th) {
                    $ttlFormatted = "{$santri->tl}, {$santri->tlhr}/{$santri->bln}/{$santri->th}";
                } elseif ($santri->tl) {
                    $ttlFormatted = $santri->tl;
                }

                // Format Alamat
                $alamatParts = array_filter([
                    $santri->Funkelurahan?->nama_kel,
                    $santri->Funkecamatan?->nama_kec,
                    $santri->Funkabupaten?->nama_kabkot ? str_replace(['KABUPATEN ', 'KOTA '], '', $santri->Funkabupaten->nama_kabkot) : null,
                    $santri->Funprovinsi?->nama_prov ? str_replace('JAWA TIMUR', 'Jatim', $santri->Funprovinsi->nama_prov) : null,
                ]);
                $alamatFormatted = !empty($alamatParts) ? implode(', ', $alamatParts) : '-';

                // Format Kelas
                $kelasDiniyyah = trim("{$santri->mkls} {$santri->mbag} {$santri->madin?->madin}");

                // Mustahiq / qoh label
                $isPutri = (int)$santri->jk === 2;
                $mustahiqLabel = $isPutri ? 'Mustahiqqoh' : 'Mustahiq';

                // Atas Prestasinya
                $targetHafalanList = \App\Models\DataHafalan::where('mkls', $santri->mkls)
                    ->where('tkt', $santri->tkt)
                    ->pluck('nama_hafalan')
                    ->implode(', ');
                
                $prestasiText = $setting->atas_prestasinya ?: 'Hafal [hafalan]';
                $prestasiText = str_replace(
                    ['[hafalan]', '[kelas]', '[jenjang]', '[tahun_ajaran]'],
                    [$targetHafalanList ?: 'Seluruh Target Wajib & Sunnah', $kelasDiniyyah, $santri->madin?->madin ?? '', $tahunAjaranNama],
                    $prestasiText
                );

                // Format Nomor Surat Otomatis per Siswa
                $nomorSurat = $setting->formatNomorSertifikat($index, [
                    '[nis]' => $santri->noin,
                    '[nama]' => $santri->nm,
                    '[kelas]' => $santri->mkls,
                    '[jenjang]' => $santri->madin?->madin ?? '',
                    '[tahun_ajaran]' => $tahunAjaranNama,
                ]);

                // Format Tanggal Masehi
                $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                if ($setting->use_current_date || blank($setting->tanggal_terbit)) {
                    $tglMasehi = date('j') . ' ' . $bulanIndo[(int)date('n')] . ' ' . date('Y') . ' M.';
                } else {
                    $tgl = \Carbon\Carbon::parse($setting->tanggal_terbit);
                    $tglMasehi = $tgl->format('j') . ' ' . $bulanIndo[(int)$tgl->format('n')] . ' ' . $tgl->format('Y') . ' M.';
                }
            @endphp

            <div class="certificate-sheet">
                <div class="sheet-inner">
                    
                    <!-- 1. Judul & Nomor Dokumen -->
                    <div class="title-block">
                        <div class="doc-title">{{ $setting->judul_sertifikat ?? 'Tanda Penghargaan' }}</div>
                        <div class="doc-nomor-wrap">
                            <div class="doc-nomor">Nomor : {{ $nomorSurat }}</div>
                        </div>
                    </div>

                    <!-- 2. Tabel Biodata Santri -->
                    <div class="biodata-section">
                        <table class="biodata-table">
                            <tr>
                                <td class="col-label">Nama</td>
                                <td class="col-sep">:</td>
                                <td class="col-val val-nama">{{ $santri->nm }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Tempat/Tgl. Lahir</td>
                                <td class="col-sep">:</td>
                                <td class="col-val">{{ $ttlFormatted }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Nama Orang Tua</td>
                                <td class="col-sep">:</td>
                                <td class="col-val val-orangtua">{{ $santri->nayah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Alamat</td>
                                <td class="col-sep">:</td>
                                <td class="col-val">{{ $alamatFormatted }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Kelas</td>
                                <td class="col-sep">:</td>
                                <td class="col-val val-kelas">{{ $kelasDiniyyah }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Mustahiq/qoh</td>
                                <td class="col-sep">:</td>
                                <td class="col-val">{{ $mustahiq }}</td>
                            </tr>
                            <tr>
                                <td class="col-label">Atas Prestasinya</td>
                                <td class="col-sep">:</td>
                                <td class="col-val">{{ $prestasiText }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- 3. Middle Section: Kotak Foto & Penandatangan 1 (Atas Kanan) -->
                    <div class="middle-section">
                        @if($setting->show_foto_box ?? true)
                            <div class="photo-box-wrap">
                                <div class="photo-box">
                                    {!! nl2br(e($setting->foto_box_label ?? "Foto\n3x4")) !!}
                                </div>
                            </div>
                        @endif

                        @if($setting->show_ttd_1 ?? true)
                            <div class="ttd1-wrap">
                                <div class="date-hijri">{{ $setting->tempat_terbit ?? 'Blokagung' }}, {{ $setting->tanggal_hijriah ?? '6 Romadhon 1447 H.' }}</div>
                                <div class="date-masehi">{{ $tglMasehi }}</div>
                                
                                <div class="ttd-jabatan">{{ $setting->ttd_1_jabatan ?? 'PKM. Muhafadhoh' }}</div>
                                
                                <div class="ttd-spacer">
                                    @if($setting->ttd_1_image_path)
                                        <img src="{{ asset('storage/' . $setting->ttd_1_image_path) }}" class="ttd-signature-img" alt="TTD">
                                    @endif
                                </div>
                                
                                <div class="ttd-nama">{{ $setting->ttd_1_nama ?? 'ANDIKO DWI SAPUTRA, S.T.T' }}</div>
                                @if($setting->ttd_1_nip)
                                    <div style="font-size: 8.5pt;">NIY. {{ $setting->ttd_1_nip }}</div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- 4. Bottom Section: Mengetahui & 2 Penandatangan Bawah -->
                    <div class="bottom-section">
                        <div class="mengetahui-label">{{ $setting->label_mengetahui ?? 'Mengetahui,' }}</div>

                        <table class="bottom-signers-table">
                            <tr>
                                <!-- Signer 2 (Bawah Kiri) -->
                                <td class="signer-col">
                                    @if($setting->show_ttd_2 ?? true)
                                        <div class="ttd-jabatan">{{ $setting->ttd_2_jabatan ?? 'Kabid. Pendidikan dan Pengajaran' }}</div>
                                        <div class="ttd-spacer">
                                            @if($setting->ttd_2_image_path)
                                                <img src="{{ asset('storage/' . $setting->ttd_2_image_path) }}" class="ttd-signature-img" alt="TTD">
                                            @endif
                                        </div>
                                        <div class="ttd-nama">{{ $setting->ttd_2_nama ?? 'DR. KH. ABDUL KHOLIQ SYAFA\'AT, MA.' }}</div>
                                        @if($setting->ttd_2_nip)
                                            <div style="font-size: 8.5pt;">NIY. {{ $setting->ttd_2_nip }}</div>
                                        @endif
                                    @endif
                                </td>

                                <!-- Signer 3 (Bawah Kanan) -->
                                <td class="signer-col">
                                    @if($setting->show_ttd_3 ?? true)
                                        <div class="ttd-jabatan">{{ $setting->ttd_3_jabatan ?? 'Kepala Madrasah' }}</div>
                                        <div class="ttd-spacer">
                                            @if($setting->stempel_image_path)
                                                <img src="{{ asset('storage/' . $setting->stempel_image_path) }}" class="stempel-overlay" alt="Stempel">
                                            @endif
                                            @if($setting->ttd_3_image_path)
                                                <img src="{{ asset('storage/' . $setting->ttd_3_image_path) }}" class="ttd-signature-img" alt="TTD">
                                            @endif
                                        </div>
                                        <div class="ttd-nama">{{ $setting->ttd_3_nama ?? 'INDY NAJMU TSAQIB, S.Pd.I' }}</div>
                                        @if($setting->ttd_3_nip)
                                            <div style="font-size: 8.5pt;">NIY. {{ $setting->ttd_3_nip }}</div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
