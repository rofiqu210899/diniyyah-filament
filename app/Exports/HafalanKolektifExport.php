<?php

namespace App\Exports;

use App\Models\HafalanSantri;
use App\Models\Mustahiq;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HafalanKolektifExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tanggalMulai;
    protected $tanggalSampai;
    protected static $mustahiqCache = [];

    public function __construct(string $tanggalMulai, string $tanggalSampai)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function query()
    {
        return HafalanSantri::query()
            ->with([
                'santri.Funkelurahan',
                'santri.Funkecamatan',
                'santri.Funkabupaten',
                'santri.Funprovinsi',
                'santri.unitSekolah',
                'dataHafalan',
                'tahunAjaran',
                'jenjang'
            ])
            ->whereDate('created_at', '>=', $this->tanggalMulai)
            ->whereDate('created_at', '<=', $this->tanggalSampai)
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'TANGGAL INPUT',
            'NIS',
            'NAMA LENGKAP',
            'TTL',
            'NAMA ORANG TUA',
            'ALAMAT',
            'KELAS DINIYYAH',
            'UNIT SEKOLAH',
            'NAMA MUSTAHIQ',
            'HAFALAN'
        ];
    }

    /**
    * @param HafalanSantri $row
    */
    public function map($row): array
    {
        $santri = $row->santri;

        // Tanggal Input
        $tanggalInput = $row->created_at 
            ? Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y H:i') 
            : '-';

        // NIS & Nama Lengkap
        $nis = $santri?->noin ?? '-';
        $namaLengkap = $santri?->nm ?? '-';

        // TTL
        $ttl = '-';
        if ($santri) {
            $bulanIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $namaBulan = $bulanIndo[(int) $santri->bln] ?? $santri->bln;
            $ttl = "{$santri->tl}, {$santri->tlhr} {$namaBulan} {$santri->th}";
        }

        // Nama Orang Tua
        $namaOrangTua = $santri?->nayah ?? '-';

        // Alamat
        $alamat = '-';
        if ($santri) {
            $alamat = "{$santri->Funkelurahan?->nama_kel}, {$santri->Funkecamatan?->nama_kec}, {$santri->Funkabupaten?->nama_kabkot}, {$santri->Funprovinsi?->nama_prov}";
        }

        // Kelas Diniyyah
        $kelasDiniyyah = "{$row->mkls} {$santri?->mbag} {$row->jenjang?->madin}";

        // Unit Sekolah
        $unitSekolah = $santri?->unitSekolah?->unit ?? '-';

        // Nama Mustahiq
        $key = "{$row->mkls}_{$santri?->mbag}_{$row->tkt}_{$santri?->jk}_{$row->tahun_ajaran_id}";
        if (!array_key_exists($key, self::$mustahiqCache)) {
            $mustahiq = Mustahiq::where('mkls', $row->mkls)
                ->where('mbag', $santri?->mbag)
                ->where('tkt', $row->tkt)
                ->where('jk', $santri?->jk)
                ->where('tahun_ajaran_id', $row->tahun_ajaran_id)
                ->first();
            self::$mustahiqCache[$key] = $mustahiq?->nama_mustahiq ?? '-';
        }
        $namaMustahiq = self::$mustahiqCache[$key];

        // Hafalan
        $hafalan = $row->dataHafalan?->nama_hafalan ?? '-';

        return [
            $tanggalInput,
            $nis,
            $namaLengkap,
            $ttl,
            $namaOrangTua,
            $alamat,
            $kelasDiniyyah,
            $unitSekolah,
            $namaMustahiq,
            $hafalan
        ];
    }
}
