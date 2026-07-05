<?php

namespace App\Exports;

use App\Models\Mustahiq;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\bukuinduk;
use App\Models\TahunAjaran;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapPertingkatanSheet implements FromView, WithTitle, ShouldAutoSize
{
    protected $tahunAjaranId;
    protected $tktId;
    protected $tktName;

    public function __construct($tahunAjaranId, $tktId, $tktName)
    {
        $this->tahunAjaranId = $tahunAjaranId;
        $this->tktId = $tktId;
        $this->tktName = $tktName;
    }

    public function title(): string
    {
        return $this->tktName;
    }

    public function view(): View
    {
        // 1. Ambil data tahun ajaran
        $tahunAjaran = TahunAjaran::find($this->tahunAjaranId);
        $tahunAjaranLabel = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : '-';

        // 2. Ambil data kelas (Mustahiq) untuk tingkatan & tahun ajaran ini
        $classes = Mustahiq::with(['madin'])
            ->where('tahun_ajaran_id', $this->tahunAjaranId)
            ->where('tkt', $this->tktId)
            ->orderBy('mkls')
            ->orderBy('mbag')
            ->orderBy('jk')
            ->get();

        // 3. Ambil daftar hafalan untuk tingkatan ini
        $hafalans = DataHafalan::where('tkt', $this->tktId)
            ->orderBy('mkls')
            ->orderBy('kriteria', 'desc') // Wajib dulu
            ->orderBy('id')
            ->get();

        // 4. Proses data per kelas
        $rowsGroupedByGrade = [];

        foreach ($classes as $class) {
            // Ambil santri di kelas ini (mkls, mbag, tkt, jk)
            $students = bukuinduk::where('mkls', $class->mkls)
                ->where('mbag', $class->mbag)
                ->where('tkt', $class->tkt)
                ->where('jk', $class->jk)
                ->get();

            $jumlahSantri = $students->count();
            $studentIds = $students->pluck('id')->toArray();

            // Ambil hafalan yang dipetakan ke kelas ini (mkls)
            $classHafalans = $hafalans->where('mkls', $class->mkls);
            $classHafalanIds = $classHafalans->pluck('id')->toArray();

            // Hitung penyelesaian hafalan per hafalan (hanya untuk hafalan kelas ini)
            $hafalanCompletions = [];
            foreach ($classHafalans as $hafalan) {
                if (count($studentIds) > 0) {
                    $count = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
                        ->whereIn('santri_id', $studentIds)
                        ->where('data_hafalan_id', $hafalan->id)
                        ->count();
                    $hafalanCompletions[$hafalan->id] = $count;
                } else {
                    $hafalanCompletions[$hafalan->id] = 0;
                }
            }

            // Hitung total hafalan kelas yang diselesaikan oleh seluruh santri
            $totalCompletedForClass = 0;
            if (count($classHafalanIds) > 0 && count($studentIds) > 0) {
                $totalCompletedForClass = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
                    ->whereIn('santri_id', $studentIds)
                    ->whereIn('data_hafalan_id', $classHafalanIds)
                    ->count();
            }

            // Statistik: Rata-rata
            $rataRata = $jumlahSantri > 0 ? round($totalCompletedForClass / $jumlahSantri, 2) : 0;

            // Statistik: Point Wajib (Jumlah santri yang tuntas semua hafalan wajib)
            $wajibHafalanIds = $classHafalans->where('kriteria', 'Wajib')->pluck('id')->toArray();
            $pointWajib = 0;
            if (count($wajibHafalanIds) > 0 && count($studentIds) > 0) {
                foreach ($students as $student) {
                    $studentWajibCount = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
                        ->where('santri_id', $student->id)
                        ->whereIn('data_hafalan_id', $wajibHafalanIds)
                        ->count();
                    if ($studentWajibCount === count($wajibHafalanIds)) {
                        $pointWajib++;
                    }
                }
            }

            // Statistik: Poin Sunnah (Jumlah santri yang menghafal minimal 1 hafalan sunnah)
            $sunnahHafalanIds = $classHafalans->where('kriteria', 'Sunnah')->pluck('id')->toArray();
            $poinSunnah = 0;
            if (count($sunnahHafalanIds) > 0 && count($studentIds) > 0) {
                foreach ($students as $student) {
                    $studentSunnahCount = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
                        ->where('santri_id', $student->id)
                        ->whereIn('data_hafalan_id', $sunnahHafalanIds)
                        ->count();
                    if ($studentSunnahCount > 0) {
                        $poinSunnah++;
                    }
                }
            }

            // Statistik: Prosentase Progres
            $prosentase = 0;
            $totalPotential = count($classHafalanIds) * $jumlahSantri;
            if ($totalPotential > 0) {
                $prosentase = round(($totalCompletedForClass / $totalPotential) * 100, 2);
            }

            // Tambahkan ke grup kelas
            $rowsGroupedByGrade[$class->mkls][] = [
                'class' => $class,
                'kelas_label' => "{$class->mkls} {$class->mbag} {$this->tktName}",
                'jk_label' => $class->jk == 1 ? 'Putra' : 'Putri',
                'mustahiq' => $class->nama_mustahiq ?? '-',
                'jumlah' => $jumlahSantri,
                'hafalan_completions' => $hafalanCompletions,
                'rata_rata' => $rataRata,
                'point_wajib' => $pointWajib,
                'poin_sunnah' => $poinSunnah,
                'prosentase' => $prosentase,
            ];
        }

        return view('exports.rekap-pertingkatan', [
            'tktName' => $this->tktName,
            'tahunAjaran' => $tahunAjaranLabel,
            'hafalansGroupedByGrade' => $hafalans->groupBy('mkls'),
            'rowsGroupedByGrade' => $rowsGroupedByGrade,
        ]);
    }
}
