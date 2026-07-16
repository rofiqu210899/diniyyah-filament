<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapMustahiqSheet implements FromView, WithTitle, ShouldAutoSize
{
    protected $tahunAjaranLabel;
    protected $mustahiq;
    protected $sheetTitle;
    protected $students;
    protected $hafalans;
    protected $hafalanRecordsGrouped;

    public function __construct(
        string $tahunAjaranLabel,
        $mustahiq,
        string $sheetTitle,
        $students,
        $hafalans,
        $hafalanRecordsGrouped
    ) {
        $this->tahunAjaranLabel = $tahunAjaranLabel;
        $this->mustahiq = $mustahiq;
        $this->sheetTitle = $sheetTitle;
        $this->students = $students;
        $this->hafalans = $hafalans;
        $this->hafalanRecordsGrouped = $hafalanRecordsGrouped;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function view(): View
    {
        $hasilRekap = $this->students->values()->map(function ($santri) {
            $tuntasIds = [];
            if (isset($this->hafalanRecordsGrouped[$santri->id])) {
                $tuntasIds = $this->hafalanRecordsGrouped[$santri->id]->pluck('data_hafalan_id')->map(fn($id) => (int) $id)->toArray();
            }

            $hafalanStatus = [];
            foreach ($this->hafalans as $hafalan) {
                $hafalanStatus[$hafalan->id] = in_array($hafalan->id, $tuntasIds);
            }

            return [
                'noin' => $santri->noin,
                'nama' => $santri->nm,
                'unit_label' => $santri->unitSekolah?->unit ?? '-',
                'hafalan' => $hafalanStatus,
            ];
        })->toArray();

        $headerHafalan = $this->hafalans->map(function ($h) {
            return [
                'id' => $h->id,
                'nama_hafalan' => $h->nama_hafalan,
                'kriteria' => $h->kriteria,
            ];
        })->toArray();

        $jkLabel = $this->mustahiq->jk == 1 ? 'Putra' : 'Putri';
        $madinLabel = $this->mustahiq->madin?->madin ?? '-';
        $filterLabel = "Kelas {$this->mustahiq->mkls}{$this->mustahiq->mbag} {$madinLabel} {$jkLabel} | Mustahiq: {$this->mustahiq->nama_mustahiq}";

        return view('exports.rekap-mustahiq-sheet', [
            'hasilRekap' => $hasilRekap,
            'headerHafalan' => $headerHafalan,
            'filterLabel' => $filterLabel,
            'tahunAjaran' => $this->tahunAjaranLabel,
        ]);
    }
}
