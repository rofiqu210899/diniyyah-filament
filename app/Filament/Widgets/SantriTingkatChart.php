<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SantriTingkatChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Santri Berdasarkan Tingkat';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        
        $data = DB::table('bukuinduk')
            ->join('madin', 'bukuinduk.tkt', '=', 'madin.id')
            ->where('bukuinduk.deleted', 0)
            ->select('madin.madin', DB::raw('count(bukuinduk.id) as total'))
            ->groupBy('madin.id', 'madin.madin')
            ->pluck('total', 'madin')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Santri',
                    'data' => array_values($data),
                    'backgroundColor' => ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
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
