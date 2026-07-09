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

    public function getData(): array
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

        // 3. Ambil daftar hafalan untuk tingkatan ini dan urutkan Wajib di paling kiri (terlebih dahulu)
        $hafalans = DataHafalan::where('tkt', $this->tktId)
            ->get()
            ->sort(function ($a, $b) {
                // Urutkan berdasarkan mkls terkecil ke terbesar
                if ($a->mkls != $b->mkls) {
                    return $a->mkls <=> $b->mkls;
                }
                // Urutkan berdasarkan kriteria: Wajib (1) dulu, Sunnah (2), Wisuda (3)
                $aOrder = match ($a->kriteria) {
                    'Wajib' => 1,
                    'Sunnah' => 2,
                    'Wisuda' => 3,
                    default => 4,
                };
                $bOrder = match ($b->kriteria) {
                    'Wajib' => 1,
                    'Sunnah' => 2,
                    'Wisuda' => 3,
                    default => 4,
                };
                if ($aOrder != $bOrder) {
                    return $aOrder <=> $bOrder;
                }
                // Urutkan berdasarkan id
                return $a->id <=> $b->id;
            })
            ->values();

        // ==========================================
        // OPTIMASI QUERY (MENCEGAH N+1)
        // ==========================================
        // Ambil semua santri di tingkatan ini sekaligus (1 Query)
        $allStudents = bukuinduk::where('tkt', $this->tktId)->get();
        $allStudentIds = $allStudents->pluck('id')->toArray();

        // Kelompokkan santri berdasarkan (mkls_mbag_jk) dalam memory untuk fast lookup
        $studentsGrouped = $allStudents->groupBy(function ($student) {
            return "{$student->mkls}_{$student->mbag}_{$student->jk}";
        });

        // Ambil semua record hafalan santri-santri ini pada tahun ajaran terpilih sekaligus (1 Query)
        $allHafalanRecords = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->whereIn('santri_id', $allStudentIds)
            ->get();

        // Kelompokkan record hafalan berdasarkan santri_id dalam memory
        $hafalanRecordsGrouped = $allHafalanRecords->groupBy('santri_id');
        // ==========================================

        // 4. Proses data per kelas
        $rowsGroupedByGrade = [];

        foreach ($classes as $class) {
            // Ambil santri untuk kelas ini dari memory
            $key = "{$class->mkls}_{$class->mbag}_{$class->jk}";
            $students = $studentsGrouped->get($key, collect());

            $jumlahSantri = $students->count();

            // Ambil hafalan yang dipetakan ke kelas ini (mkls)
            $classHafalans = $hafalans->where('mkls', $class->mkls);
            $classHafalanIds = $classHafalans->pluck('id')->toArray();

            // Hitung penyelesaian hafalan per hafalan (hanya untuk hafalan kelas ini)
            $hafalanCompletions = [];
            foreach ($classHafalans as $hafalan) {
                $count = 0;
                foreach ($students as $student) {
                    $completedIds = $hafalanRecordsGrouped->get($student->id, collect())
                        ->pluck('data_hafalan_id')
                        ->all();
                    if (in_array($hafalan->id, $completedIds)) {
                        $count++;
                    }
                }
                $hafalanCompletions[$hafalan->id] = $count;
            }

            // Hitung total hafalan kelas yang diselesaikan oleh seluruh santri
            $totalCompletedForClass = 0;
            foreach ($students as $student) {
                $completedIds = $hafalanRecordsGrouped->get($student->id, collect())
                    ->pluck('data_hafalan_id')
                    ->all();
                $completedForClass = array_intersect($classHafalanIds, $completedIds);
                $totalCompletedForClass += count($completedForClass);
            }

            // Statistik: Rata-rata
            $rataRata = $jumlahSantri > 0 ? round($totalCompletedForClass / $jumlahSantri, 2) : 0;

            // Statistik: Wajib (Jumlah & Prosentase)
            $wajibHafalanIds = $classHafalans->where('kriteria', 'Wajib')->pluck('id')->toArray();
            $wajibCompletedCount = 0;
            foreach ($wajibHafalanIds as $id) {
                $wajibCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $wajibPotential = count($wajibHafalanIds) * $jumlahSantri;
            $wajibProsentase = $wajibPotential > 0 ? round(($wajibCompletedCount / $wajibPotential) * 100, 2) : 0;

            // Statistik: Sunnah (Jumlah & Prosentase)
            $sunnahHafalanIds = $classHafalans->where('kriteria', 'Sunnah')->pluck('id')->toArray();
            $sunnahCompletedCount = 0;
            foreach ($sunnahHafalanIds as $id) {
                $sunnahCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $sunnahPotential = count($sunnahHafalanIds) * $jumlahSantri;
            $sunnahProsentase = $sunnahPotential > 0 ? round(($sunnahCompletedCount / $sunnahPotential) * 100, 2) : 0;

            // Statistik: Wisuda (Jumlah & Prosentase)
            $wisudaHafalanIds = $classHafalans->where('kriteria', 'Wisuda')->pluck('id')->toArray();
            $wisudaCompletedCount = 0;
            foreach ($wisudaHafalanIds as $id) {
                $wisudaCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $wisudaPotential = count($wisudaHafalanIds) * $jumlahSantri;
            $wisudaProsentase = $wisudaPotential > 0 ? round(($wisudaCompletedCount / $wisudaPotential) * 100, 2) : 0;

            // Statistik: Prosentase Progres Total
            $prosentase = 0;
            $totalPotential = count($classHafalanIds) * $jumlahSantri;
            if ($totalPotential > 0) {
                $prosentase = round(($totalCompletedForClass / $totalPotential) * 100, 2);
            }

            // Tambahkan ke grup kelas
            $rowsGroupedByGrade[$class->mkls][] = [
                'class' => $class,
                'kelas_label' => "{$class->mkls}{$class->mbag} {$this->tktName}",
                'jk_label' => $class->jk == 1 ? 'Putra' : 'Putri',
                'mustahiq' => $class->nama_mustahiq ?? '-',
                'jumlah' => $jumlahSantri,
                'hafalan_completions' => $hafalanCompletions,
                'rata_rata' => $rataRata,
                'wajib_jumlah' => $wajibCompletedCount,
                'wajib_prosentase' => $wajibProsentase,
                'sunnah_jumlah' => $sunnahCompletedCount,
                'sunnah_prosentase' => $sunnahProsentase,
                'wisuda_jumlah' => $wisudaCompletedCount,
                'wisuda_prosentase' => $wisudaProsentase,
                'prosentase' => $prosentase,
            ];
        }

        return [
            'tktName' => $this->tktName,
            'tahunAjaran' => $tahunAjaranLabel,
            'hafalansGroupedByGrade' => $hafalans->groupBy('mkls'),
            'rowsGroupedByGrade' => $rowsGroupedByGrade,
        ];
    }

    public function view(): View
    {
        return view('exports.rekap-pertingkatan', $this->getData());
    }
}
