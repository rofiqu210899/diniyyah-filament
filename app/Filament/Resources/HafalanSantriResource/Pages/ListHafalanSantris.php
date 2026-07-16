<?php

namespace App\Filament\Resources\HafalanSantriResource\Pages;

use App\Filament\Resources\HafalanSantriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HafalanKolektifExport;

class ListHafalanSantris extends ListRecords
{
    protected static string $resource = HafalanSantriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Ekspor')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->required(),
                    DatePicker::make('tanggal_sampai')
                        ->label('Tanggal Sampai')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $tanggalMulai = $data['tanggal_mulai'];
                    $tanggalSampai = $data['tanggal_sampai'];
                    
                    return Excel::download(
                        new HafalanKolektifExport($tanggalMulai, $tanggalSampai),
                        "hafalan-kolektif-{$tanggalMulai}-to-{$tanggalSampai}.xlsx"
                    );
                })
        ];
    }
}
