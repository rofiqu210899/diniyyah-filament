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
        
        $tahunAjaran = TahunAjaran::find($this->tahunAjaranId);
        $tahunAjaranLabel = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : '-';

        
        $classes = Mustahiq::with(['madin'])
            ->where('tahun_ajaran_id', $this->tahunAjaranId)
            ->where('tkt', $this->tktId)
            ->orderBy('mkls')
            ->orderBy('mbag')
            ->orderBy('jk')
            ->get();

        
        $hafalans = DataHafalan::where('tkt', $this->tktId)
            ->get()
            ->sort(function ($a, $b) {
                
                if ($a->mkls != $b->mkls) {
                    return $a->mkls <=> $b->mkls;
                }
                
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
                
                return $a->id <=> $b->id;
            })
            ->values();

        
        
        
        
        $allStudents = bukuinduk::where('tkt', $this->tktId)->get();
        $allStudentIds = $allStudents->pluck('id')->toArray();

        
        $studentsGrouped = $allStudents->groupBy(function ($student) {
            return "{$student->mkls}_{$student->mbag}_{$student->jk}";
        });

        
        $allHafalanRecords = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->whereIn('santri_id', $allStudentIds)
            ->get();

        
        $hafalanRecordsGrouped = $allHafalanRecords->groupBy('santri_id');
        

        
        $rowsGroupedByGrade = [];

        foreach ($classes as $class) {
            
            $key = "{$class->mkls}_{$class->mbag}_{$class->jk}";
            $students = $studentsGrouped->get($key, collect());

            $jumlahSantri = $students->count();

            
            $classHafalans = $hafalans->where('mkls', $class->mkls);
            $classHafalanIds = $classHafalans->pluck('id')->toArray();

            
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

            
            $totalCompletedForClass = 0;
            foreach ($students as $student) {
                $completedIds = $hafalanRecordsGrouped->get($student->id, collect())
                    ->pluck('data_hafalan_id')
                    ->all();
                $completedForClass = array_intersect($classHafalanIds, $completedIds);
                $totalCompletedForClass += count($completedForClass);
            }

            
            $rataRata = $jumlahSantri > 0 ? round($totalCompletedForClass / $jumlahSantri, 2) : 0;

            
            $wajibHafalanIds = $classHafalans->where('kriteria', 'Wajib')->pluck('id')->toArray();
            $wajibCompletedCount = 0;
            foreach ($wajibHafalanIds as $id) {
                $wajibCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $wajibPotential = count($wajibHafalanIds) * $jumlahSantri;
            $wajibProsentase = $wajibPotential > 0 ? round(($wajibCompletedCount / $wajibPotential) * 100, 2) : 0;

            
            $sunnahHafalanIds = $classHafalans->where('kriteria', 'Sunnah')->pluck('id')->toArray();
            $sunnahCompletedCount = 0;
            foreach ($sunnahHafalanIds as $id) {
                $sunnahCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $sunnahPotential = count($sunnahHafalanIds) * $jumlahSantri;
            $sunnahProsentase = $sunnahPotential > 0 ? round(($sunnahCompletedCount / $sunnahPotential) * 100, 2) : 0;

            
            $wisudaHafalanIds = $classHafalans->where('kriteria', 'Wisuda')->pluck('id')->toArray();
            $wisudaCompletedCount = 0;
            foreach ($wisudaHafalanIds as $id) {
                $wisudaCompletedCount += $hafalanCompletions[$id] ?? 0;
            }
            $wisudaPotential = count($wisudaHafalanIds) * $jumlahSantri;
            $wisudaProsentase = $wisudaPotential > 0 ? round(($wisudaCompletedCount / $wisudaPotential) * 100, 2) : 0;

            
            $prosentase = 0;
            $totalPotential = count($classHafalanIds) * $jumlahSantri;
            if ($totalPotential > 0) {
                $prosentase = round(($totalCompletedForClass / $totalPotential) * 100, 2);
            }

            
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
