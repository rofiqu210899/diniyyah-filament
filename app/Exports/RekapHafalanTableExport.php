<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekapHafalanTableExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $hasilRekap;
    protected array $headerHafalan;
    protected string $filterLabel;
    protected string $jenisPendidikan;

    public function __construct(array $hasilRekap, array $headerHafalan, string $filterLabel, string $jenisPendidikan)
    {
        $this->hasilRekap = $hasilRekap;
        $this->headerHafalan = $headerHafalan;
        $this->filterLabel = $filterLabel;
        $this->jenisPendidikan = $jenisPendidikan;
    }

    public function title(): string
    {
        return 'Rekap Hafalan';
    }

    public function view(): View
    {
        return view('exports.rekap-hafalan-table', [
            'hasilRekap' => $this->hasilRekap,
            'headerHafalan' => $this->headerHafalan,
            'filterLabel' => $this->filterLabel,
            'jenisPendidikan' => $this->jenisPendidikan,
        ]);
    }
}
