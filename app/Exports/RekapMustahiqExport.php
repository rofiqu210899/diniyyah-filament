<?php

namespace App\Exports;

use App\Models\Mustahiq;
use App\Models\bukuinduk;
use App\Models\DataHafalan;
use App\Models\HafalanSantri;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapMustahiqExport implements WithMultipleSheets
{
    protected $tahunAjaranId;
    protected $tktId;

    public function __construct($tahunAjaranId, $tktId = null)
    {
        $this->tahunAjaranId = $tahunAjaranId;
        $this->tktId = $tktId;
    }

    public function sheets(): array
    {
        $tahunAjaran = TahunAjaran::find($this->tahunAjaranId);
        $tahunAjaranLabel = $tahunAjaran ? $tahunAjaran->nama_tahun_ajaran : '-';

        // 1. Eager load all mustahiqs for this school year (filtered by tktId if provided)
        $mustahiqs = Mustahiq::with(['madin'])
            ->where('tahun_ajaran_id', $this->tahunAjaranId)
            ->when($this->tktId, fn($q) => $q->where('tkt', $this->tktId))
            ->orderBy('tkt')
            ->orderBy('mkls')
            ->orderBy('mbag')
            ->orderBy('jk')
            ->get();

        // Get arrays of IDs to restrict eager loading query sizes
        $tktIds = $mustahiqs->pluck('tkt')->unique()->toArray();

        if (empty($tktIds)) {
            return [];
        }

        // 2. Load all students and group by tkt_mkls_mbag_jk in memory
        $allStudents = bukuinduk::with(['unitSekolah'])
            ->whereIn('tkt', $tktIds)
            ->get();

        $studentsGrouped = $allStudents->groupBy(function ($s) {
            return "{$s->tkt}_{$s->mkls}_{$s->mbag}_{$s->jk}";
        });

        // 3. Load all target hafalans and group by tkt_mkls in memory
        $allDataHafalan = DataHafalan::whereIn('tkt', $tktIds)->get();
        $hafalansGrouped = $allDataHafalan->groupBy(function ($h) {
            return "{$h->tkt}_{$h->mkls}";
        });

        // 4. Load all completions for the active school year
        $allStudentIds = $allStudents->pluck('id')->toArray();
        $allHafalanRecords = HafalanSantri::where('tahun_ajaran_id', $this->tahunAjaranId)
            ->whereIn('santri_id', $allStudentIds)
            ->get();

        $hafalanRecordsGrouped = $allHafalanRecords->groupBy('santri_id');

        $sheets = [];
        foreach ($mustahiqs as $mustahiq) {
            $gender = $mustahiq->jk == 1 ? 'PA' : 'PI';
            $madinLabel = $mustahiq->madin?->madin ?? '';
            // E.g. "1A ULA PA"
            $sheetTitle = trim("{$mustahiq->mkls}{$mustahiq->mbag} {$madinLabel} {$gender}");
            
            // Clean sheet title (excel restrictions)
            $sheetTitle = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '', $sheetTitle);
            $sheetTitle = substr($sheetTitle, 0, 31);

            // Fetch preloaded data for this specific mustahiq class
            $key = "{$mustahiq->tkt}_{$mustahiq->mkls}_{$mustahiq->mbag}_{$mustahiq->jk}";
            $students = $studentsGrouped->get($key, collect())->sortBy('nm');

            $hafalanKey = "{$mustahiq->tkt}_{$mustahiq->mkls}";
            $hafalans = $hafalansGrouped->get($hafalanKey, collect())
                ->sort(function ($a, $b) {
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

            $sheets[] = new RekapMustahiqSheet(
                $tahunAjaranLabel,
                $mustahiq,
                $sheetTitle,
                $students,
                $hafalans,
                $hafalanRecordsGrouped
            );
        }

        return $sheets;
    }
}
