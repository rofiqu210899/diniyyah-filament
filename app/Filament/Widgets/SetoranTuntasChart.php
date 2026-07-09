<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SetoranTuntasChart extends ChartWidget
{
    protected static ?string $heading = 'Total Setoran Hafalan Tuntas Per Tahun Ajaran';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        
        $data = DB::table('hafalan_santris')
            ->join('tahun_ajarans', 'hafalan_santris.tahun_ajaran_id', '=', 'tahun_ajarans.id')
            ->select('tahun_ajarans.nama_tahun_ajaran', DB::raw('count(hafalan_santris.id) as total'))
            ->groupBy('tahun_ajarans.id', 'tahun_ajarans.nama_tahun_ajaran')
            ->orderBy('tahun_ajarans.nama_tahun_ajaran', 'asc')
            ->pluck('total', 'nama_tahun_ajaran')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Setoran Hafalan',
                    'data' => array_values($data),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
